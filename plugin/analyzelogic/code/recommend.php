<?php

return '<div class="article-recommend-wrap">
    <a class="article-recommend"  href="'.$this->get_efs_url($res_data['id'],$res_data['catid'],$res_data['language']).'" target="_blank" rel="noopener noreferrer">

        <div class="article-recommend-thumb">
             <img src="'.$img_url.'"  width="400" height="200" alt="'.$res_data['title'].'">
        </div>
        <div class="article-recommend-info">
            <div class="article-recommend-title">'.$res_data['title'].'</div>
            <p>'.$res_data['metadesc'].'</p>
            <div class="article-recommend-readmore">'.JText::_("TPL_MINITOOL_READMODRE").'</div>
        </div>
    </a>
    </div>
'


?>
