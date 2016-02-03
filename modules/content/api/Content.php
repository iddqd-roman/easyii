<?php
namespace yii\easyii\modules\content\api;

use Yii;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\data\ActiveDataProvider;
use yii\easyii\modules\content\models\Item;
use yii\easyii\modules\content\models\Layout;
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
 * @method static void plugin() Applies FancyBox widgetClass on photos called by box() function
 * @method static void nav($navBarConfig = [], $navConfig = []) Applies NavBar widgetClass
 * @method static string pages() returns pagination html generated by yii\widgets\LinkPager widgetClass.
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

	/**
	 * @param array $options
	 *
	 * @return ItemObject[]
	 */
	public function api_items($options = [])
	{
		if (!$this->_items) {
			$this->_items = [];

			$query = Item::find()->with(['seo', 'layout'])->status(Item::STATUS_ON);

			if (!empty($options['where'])) {
				$query->andFilterWhere($options['where']);
			}
			if (isset($options['status'])) {
				$query->status($options['status']);
			}
			if (isset($options['nav'])) {
				$query->andWhere(['nav' => (int)$options['nav']]);
			}
			if (isset($options['depth'])) {
				$query->andWhere(['depth' => (int)$options['depth']]);
			}
			if (!empty($options['orderBy'])) {
				$query->orderBy($options['orderBy']);
			}
			else {
				$query->sort();
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
				$item = new ItemObject($model);
				$this->_items[] = $item;
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

	public function api_nav(array $navBarConfig = [], array $navConfig = [])
	{
		$navBarConfig = array_merge_recursive([
				'id' => 'nav',
				'options' => [
					'class' => 'navbar navbar-default',
				],
			],
			$navBarConfig);

		NavBar::begin($navBarConfig);

		$menuItems = [];

		$items = $this->api_items(['nav' => Item::NAV_ON, 'depth' => 0]);
		foreach ($items as $item)
		{
			$menuItem = [
				'label' => $item->model->title,
				'url' => ['/' . str_replace('-', '/', $item->slug)],
			];

			if (count($item->getChildren(['nav' => Item::NAV_ON])) > 0)
			{
				$subItems = [];
				foreach ($item->getChildren() as $child)
				{
					$subItems[] = [
						'label' => $child->model->title,
						'url' => ['/' . str_replace('-', '/', $child->slug)],
					];
				}

				$menuItem['items'] = $subItems;
			}


			$menuItems[] = $menuItem;
		}

		$navConfig = array_merge_recursive([
				'options' => ['class' => 'navbar-nav'],
				'items' => $menuItems,
			],
			$navConfig);

		echo Nav::widget($navConfig);

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
		}
		return $query;
	}


	private function findLayout($id_slug)
	{
		$layout = Layout::find()->where(['or', 'category_id=:id_slug', 'slug=:id_slug'], [':id_slug' => $id_slug])->status(Item::STATUS_ON)->one();

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