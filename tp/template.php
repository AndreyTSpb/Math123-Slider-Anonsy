<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 12/07/2021
 * Time: 22:51
 */
extract($data);
?>
<h2 class="text-white"><?=(!empty($title))?$title:'анонсы мероприятий';?></h2>
<div class="<?=(!empty($subject))?$subject:'phyth';?>-anons-wrap page-anonse" id="page-anonse-<?=$id_slider;?>">
    <?php if(!empty($posts)):?>

        <?php foreach ($posts as $post):?>
            <div>
                <div class="card-base-anonce">
                    <h4><?=(!empty($post['title']))?$post['title']:'Net zagolovka';?></h4>
                    <div class="card-base-anonce-body">
                        <?=(!empty($post['img']))?$post['img']:'<img src="https://via.placeholder.com/300x220">'?>
                        <p><?=(!empty($post['desc']))?$post['desc']:'Net opisania';?></p>
                        <a href="<?=(!empty($post['url']))?$post['url']:'#';?>" class="btn-sub green">Подробней</a>
                    </div>
                </div>
            </div>
        <?php endforeach;?>

    <?php endif;?>
</div>