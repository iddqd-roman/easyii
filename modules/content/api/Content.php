<?php
namespace yii\easyii\modules\content\api;

use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\easyii\modules\content\models\Item;
use yii\easyii\modules\content\models\ItemData;
use yii\easyii\modules\content\models\Layout;
use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\widgets\Fancybox;
use yii\widgets\LinkPager;

/**
 * Catalog module API
 * @package app\modules\content\api
 *
 * @method static LayoutObject cat(mixed $id_slug) Get catalog layout by id or slug
 * @method static array tree() Get catalog categories as tree
 * @method static array cats() Get catalog categories as flat array
 * @method static ItemObject[] items(array $options = []) Get list of items as ItemObject objects
 * @method static ItemObject get(mixed $id_slug) Get item object by id or slug
 * @method static mixed last(int $limit = 1, mixed $where = null) Get last items, use $where option for fetching items from special layout
 * @method static void plugin() Applies FancyBox widget on photos called by box() function
 * @method static void nav() Applies NavBar widget
 * @method static string pages() returns pagination html generated by yii\widgets\LinkPager widget.
 * @method static \stdClass pagination() returns yii\data\Pagination object.
 */
class Content extends \yii\easyii\components\API
{
	private $_cats;
	private $_items;
	private $_adp;
	private $_item = [];
	private $_last;

	public function api_cat($id_slug)
	{
		if (!isset($this->_cats[$id_slug])) {
			$this->_cats[$id_slug] = $this->findLayout($id_slug);
		}
		return $this->_cats[$id_slug];
	}

	public function api_tree()
	{
		return Layout::tree();
	}

	public function api_cats()
	{
		return Layout::cats();
	}

	public function api_items($options = [])
	{
		if (!$this->_items) {
			$this->_items = [];

			$query = Item::find()->with(['seo', 'layout'])->status(Item::STATUS_ON);

			if (!empty($options['where'])) {
				$query->andFilterWhere($options['where']);
			}
			if (!empty($options['status'])) {
				$query->status($options['status']);
			}
			if (!empty($options['nav'])) {
				$query->andWhere(['nav' => (int)$options['nav']]);
			}
			if (!empty($options['orderBy'])) {
				$query->orderBy($options['orderBy']);
			}
			else {
				$query->sortDate();
			}
			if (!empty($options['filters'])) {
				$query = self::applyFilters($options['filters'], $query);
			}

			$paginationOptions = [
				'pageParam' => 'content[page]',
				'pageSizeParam' => 'content[per-pag]'
			];
			if (!empty($options['pagination'])) {
				$paginationOptions = array_merge($paginationOptions, $options['pagination']);
			}

			$this->_adp = new ActiveDataProvider([
				'query' => $query,
				'pagination' => $paginationOptions
			]);

			foreach ($this->_adp->models as $model) {
				$this->_items[] = new ItemObject($model);
			}
		}
		return $this->_items;
	}

	public function api_last($limit = 1, $where = null)
	{
		if ($limit === 1 && $this->_last) {
			return $this->_last;
		}

		$result = [];

		$query = Item::find()->with('seo')->sortDate()->status(Item::STATUS_ON)->limit($limit);
		if ($where) {
			$query->andFilterWhere($where);
		}

		foreach ($query->all() as $item) {
			$result[] = new ItemObject($item);
		}

		if ($limit > 1) {
			return $result;
		}
		else {
			$this->_last = count($result) ? $result[0] : null;
			return $this->_last;
		}
	}

	public function api_get($id_slug)
	{

		if (!isset($this->_item[$id_slug])) {
			$this->_item[$id_slug] = $this->findItem($id_slug);
		}
		return $this->_item[$id_slug];
	}

	public function api_pagination()
	{
		return $this->_adp ? $this->_adp->pagination : null;
	}

	public function api_pages()
	{
		return $this->_adp ? LinkPager::widget(['pagination' => $this->_adp->pagination]) : '';
	}

	public function api_plugin($options = [])
	{
		Fancybox::widget([
			'selector' => '.easyii-box',
			'options' => $options
		]);
	}

	public function api_nav()
	{
		NavBar::begin([
			'id' => 'nav',
			'options' => [
				'class' => 'navbar navbar-default',
			],
		]);

		$menuItems = [];

		$items = $this->api_items(['nav' => Item::NAV_ON, 'orderBy' => ['time' => SORT_ASC]]);
		foreach ($items as $item)
		{
			$menuItems[] = ['label' => $item->model->title, 'url' => ['/' . str_replace('-', '/', $item->slug)]];
		}

		echo Nav::widget([
			'items' => $menuItems,
		]);

		NavBar::end();
	}

	public static function applyFilters($filters, $query)
	{
		if (is_array($filters)) {

			if (!empty($filters['price'])) {
				$price = $filters['price'];
				if (is_array($price) && count($price) == 2) {
					if (!$price[0]) {
						$query->andFilterWhere(['<=', 'price', (int)$price[1]]);
					}
					elseif (!$price[1]) {
						$query->andFilterWhere(['>=', 'price', (int)$price[0]]);
					}
					else {
						$query->andFilterWhere(['between', 'price', (int)$price[0], (int)$price[1]]);
					}
				}
				unset($filters['price']);
			}
			if (count($filters)) {
				$filtersApplied = 0;
				$subQuery = ItemData::find()->select('item_id, COUNT(*) as filter_matched')->groupBy('item_id');
				foreach ($filters as $field => $value) {
					if (!is_array($value)) {
						$subQuery->orFilterWhere(['and', ['name' => $field], ['value' => $value]]);
						$filtersApplied++;
					}
					elseif (count($value) == 2) {
						if (!$value[0]) {
							$additionalCondition = ['<=', 'value', (int)$value[1]];
						}
						elseif (!$value[1]) {
							$additionalCondition = ['>=', 'value', (int)$value[0]];
						}
						else {
							$additionalCondition = ['between', 'value', (int)$value[0], (int)$value[1]];
						}
						$subQuery->orFilterWhere(['and', ['name' => $field], $additionalCondition]);

						$filtersApplied++;
					}
				}
				if ($filtersApplied) {
					$query->join('LEFT JOIN', ['f' => $subQuery], 'f.item_id = ' . Item::tableName() . '.item_id');
					$query->andFilterWhere(['f.filter_matched' => $filtersApplied]);
				}
			}
		}
		return $query;
	}


	private function findLayout($id_slug)
	{
		$layout = Item::find()->where(['or', 'category_id=:id_slug', 'slug=:id_slug'], [':id_slug' => $id_slug])->status(Item::STATUS_ON)->one();

		return $layout ? new LayoutObject($layout) : null;
	}

	private function findItem($id_slug)
	{
		if (!($item = Item::find()->where(['or', 'item_id=:id_slug', 'slug=:id_slug'], [':id_slug' => $id_slug])->status(Item::STATUS_ON)->one())) {
			return null;
		}

		return new ItemObject($item);
	}
}