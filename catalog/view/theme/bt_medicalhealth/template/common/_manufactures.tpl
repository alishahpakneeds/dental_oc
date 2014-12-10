<?php $this->load->model('catalog/manufacturer'); ?>
<?php
$manufactures = $this->model_catalog_manufacturer->getManufacturers();

?>
<div id="column-left" class="four columns">
    <div class="main-column-left">
        <div class="box category">
            <div class="box-heading">Manufactures </div>
            <div class="box-content">
                <div class="box-category">
                    <ul>
                        <?php
                        foreach($manufactures as $manuf){
                        ?>
                        <li>
                            <a href="?route=product/category/&manuf=<?php echo $manuf['manufacturer_id']; ?>"><?php echo $manuf['name']; ?></a>
                        </li>

                        <?php
                        }

                        ?>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</div>
