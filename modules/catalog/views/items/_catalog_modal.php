<?php
/* @var $id string */
/* @var $title string */

use yii\bootstrap\Modal;
use yii\helpers\Url;

Modal::begin([
    'header' => '<h3 class="text-center">' . $title . '</h3>',
    'id' => 'modal-' . $id,
    'size' => Modal::SIZE_LARGE,
    'options' => [
        'data-id' => $id
    ]
]);
Modal::end();

$url = Url::to(['/admin/catalog/items/catalog-field']);

if(!defined('CATALOG_FIELD_REGISTERED')){
    define('CATALOG_FIELD_REGISTERED', true);
    $this->registerJs(<<<JS
    jQuery('.catalog-choose').on('click', function(){
        var modal_selector = jQuery(this).data('target'),
            modal_obj = jQuery(modal_selector);
        modal_obj.find('.modal-body').load('$url', {type:'categories'});
    });
    window.catalogFieldLoad = function(el, type, category_id){
        var data = {
            type : type,
            category_id : category_id
        };
        var modal_body = jQuery(el).parentsUntil('.modal-dialog').filter('.modal-body');
        var id = modal_body
            .parentsUntil('.modal')
            .parent()
            .data('id');
        
        modal_body.load('$url', data, function(){
            if(type !== 'items'){
                return;
            }
            var checkboxes = modal_body.find('input[type="checkbox"]');
            jQuery('#catalog-field-'+id).find('.chosen-item').each(function(){
                checkboxes
                    .filter('[data-id="'+jQuery(this).data('id')+'"]')
                    .attr('checked', true);
            });
            catalogFieldSort(1);
        });
    };
    window.catalogFieldAddItem = function(el, id, title){
        var el_obj = jQuery(el);
        var field_id = el_obj
            .parentsUntil('.modal')
            .parent()
            .data('id');
        var container_of_chosen = jQuery('#catalog-field-' + field_id);
        if(el_obj.is(':checked')){
            var html = '<div class="alert alert-info fade in chosen-item" data-id="'+id+'">';
            html += '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>';
            html += title;
            html += '<input type="hidden" name="Data['+field_id+'][]" value="'+id+'">';
            html += '</div>';
            container_of_chosen.append(html);
            return;
        }
        container_of_chosen
            .find('[data-id="'+id+'"]')
            .remove();
    };
            
    window.catalogFieldSort = function(n, int_type = false) {
        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById('catalog-table');
        switching = true;
        dir = "asc"; 
        while (switching) {
          switching = false;
          rows = table.getElementsByTagName("tr");
          for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("td")[n];
            y = rows[i + 1].getElementsByTagName("td")[n];
            if (dir == "asc") {
                if(int_type){
                    if(parseInt(x.innerHTML) > parseInt(y.innerHTML)){
                        shouldSwitch = true;
                        break;
                    }
                }
             else if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                shouldSwitch= true;
                break;
              }
            } else if (dir == "desc") {
                if(int_type){
                    if(parseInt(x.innerHTML) < parseInt(y.innerHTML)){
                        shouldSwitch = true;
                        break;
                    }
                }
              else if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                shouldSwitch= true;
                break;
              }
            }
          }
          if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount ++; 
          } else {
            if (switchcount == 0 && dir == "asc") {
              dir = "desc";
              switching = true;
            }
          }
        }
      }
    
JS
);
}


