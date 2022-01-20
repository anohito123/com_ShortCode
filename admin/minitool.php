<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2019 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

?>


<style>
    label{
        display: inline-block;
       min-width: 100px;
        vertical-align: middle;

    }

    .toggle-editor{
        display: none;
    }
    .js-editor-tinymce{
        margin-bottom: 15px;
    }
    #tinymce_ifr{
        height: auto !important;
    }
    .mce-edit-area {
        width: 500px;
        height: 120px;
        overflow: auto;
    }
    .mce-menubar{
        display: none;
    }
    .mce-toolbar-grp{
        display: none;
    }

    .mce-tinymce{
        margin-bottom: 20px;
    }

    textarea {
        display: inline-block;
        border: #0a0e14 solid 1px;
        overflow-y: auto;
        vertical-align: middle;
        width: 500px ;height: 100px; border: #0a0e14 solid 1px;
    }

    .cont {

        padding:0;
        display:none;
        text-align:left;
        left: 10%;

    }
    .tab {
        font-size: 14px;
        width: 600px;
        margin:0;
        padding:0;
        list-style:none;
        overflow:hidden;
    }
    .tab li {
        float:left;
        width:120px;
        height:30px;

        text-align:center;
        line-height:30px;
        cursor:pointer;
    }
    .on {
        display:block;
    }
    .tab li.cur {
        color: #fff;
        font-weight: bolder;
        background-color: #6b6b6b;
    }
    #ist{
        margin-top: 20px;
        display:block;
    }

    #add_elm{
        position:relative;
        left: 95%;
    }

    .err_tip{
        display: none;
        color: red;
        margin-left: 17%;
        margin-bottom: 9px;
    }
</style>
<div style="margin: 0 auto; margin-top:20px; width:80%; text-align: center;">
<div>
    <ul class="tab">
        <li class="cur" data-name="li_rec" >推荐文章</li>
        <li data-name="li_ctb">文本框</li>
        <li data-name="li_twt">插入Twitter按钮</li>
        <li data-name="li_faq">FAQ</li>
        <li data-name="li_rec_out">站外推荐文章</li>

    </ul><br>
</div>
    <div>
        <div class="cont on">
            <label for="body">文章别名：</label>
            <input id="body" type="text">
            <div class="err_tip" id="rec_tip"></div>
     </div>

    <div class="cont">
        <label for="preview">文本框内容：</label>

        <textarea  id="basic-tn"></textarea>
<!--        <textarea id="contentbox"></textarea><br>-->

        <label for="cbtype">文本框类型：</label>
        <select id="cbtype">
            <option value="none" selected>无</option>
            <option value='tip' >提示</option>
            <option value="note" >注释</option>
            <option value="warn" >警告</option>
        </select><br>
        <label for="cbcss"> 边框样式:</label>
        <select id="cbcss">
            <option value="none" selected>无</option>
            <option value="solid" >实线</option>
            <option value="dashed" >虚线</option>
        </select><br><br>



    </div>

    <div class="cont">
<!--        <label for="twurl"> URL：</label>-->
<!--        <input type="text" id="twurl"><br>-->
        <label for="twtype"> 类型：</label>
        <select id="twtype">
            <option value="basic" selected>基础按钮</option>
            <option value="text" >文本框按钮</option>
        </select><br>
        <div id="twshow">
        <label for="twcontent"> 文本框按钮内容：</label>
        <textarea id="twcontent"></textarea><br>
        </div>
    </div>

        <div class="cont" id="add_div">
<!--            <button id="add_elm" onclick="add_faq()">添加FAQ</button>-->
            <!--        <label for="twurl"> URL：</label>-->
            <!--        <input type="text" id="twurl"><br>-->
            <div>
            <div>
            <label for="faqtitle1"> 问题1：</label>
            <input id="faqtitle1" type="text">
            </div>
            <div>
                <label for="basic-tw1">答案1：</label>
                <textarea class="tw" id="basic-tw1"></textarea>
            </div>
            </div>
            <button id="add_elm" onclick="add_faq()">添加FAQ</button>

        </div>

        <div class="cont">
            <!--        <label for="twurl"> URL：</label>-->
            <!--        <input type="text" id="twurl"><br>-->
            <label for="recommend-url"> 推荐连接：</label>
            <input type="text" id="recommend-url">
            <div class="err_tip" id="rec_out_tip"></div>
            <div>
                <label for="recommend-title">标题：</label>
                <input type="text" id="recommend-title">
            </div>
            <div>
                <label for="recommend-desc">描述：</label>
                <textarea class="tw" id="recommend-desc"></textarea>
            </div>
        </div>

        </div>
    <button id="ist">插入简码</button>

</div>



<script>
    jQuery(document).ready(function() {
        jQuery("#twshow").hide();
        jQuery(".tab li").click(function() {
            jQuery(".tab li").eq(jQuery(this).index()).addClass("cur").siblings().removeClass('cur');
            jQuery(".cont").hide().eq(jQuery(this).index()).show();
        });

        jQuery("#twtype").change(function () {
            if(jQuery("#twtype").val()=='basic'){
                jQuery("#twshow").hide();
            }else{
                jQuery("#twshow").show();
            }
        });

        jQuery("#ist").click(function (event) {
            var host = window.document.location.host
            var protocol = window.location.protocol
            event.preventDefault();
            if(jQuery(".cur").attr('data-name')=='li_rec' && jQuery("#body").val()!=''){

                jQuery.post(protocol+"//"+host+"/index.php",
                    {
                        option:"com_ajax",
                        plugin:"minitoolajax",
                        format:"json",
                        method:"getArtByAlias",
                        alias:jQuery("#body").val()
                    },
                    function(data,status){
                        //console.log("data:" + data+ "\nstate:" + status);
                         console.log(status);
                        if(data.data!='[]' && data.data!=''){

                            insert_code('[recommend link="'+jQuery("#body").val()+'"][/recommend]');
                        }else{
                            jQuery("#rec_tip").text("文章别名不存在！");
                            jQuery("#rec_tip").show(150)
                        }
                    });
            }

            //站外推荐文章
            if(jQuery(".cur").attr('data-name')=='li_rec_out' && jQuery("#recommend-url").val()!=''){

                var match = /^((http|https):\/\/)+(www.minitool.com|de.minitool.com|jp.minitool.com|www.minitool.fr|www.partitionwizard.com|www.partitionwizard.jp|youtubedownload.minitool.com|moviemaker.minitool.com|videoconvert.minitool.com|mt-3.com|mt.com)/;
                var is_in_site = match.test(jQuery("#recommend-url").val());
                if(is_in_site){

                    insert_code('[extrecommend  link="'+jQuery("#recommend-url").val()+'"  ' +
                        'title="'+jQuery("#recommend-title").val()+'" ' +
                        'desc="'+jQuery("#recommend-desc").val()+'"][/extrecommend]')

                }else{
                    jQuery("#rec_out_tip").text("链接有误或非我司网站链接！");
                    jQuery("#rec_out_tip").show(150)

                }

            }

        })

    });
</script>
<!--<script src="../media/system/js/core.js" ></script>-->
<script src="../media/vendor/tinymce/tinymce.js" ></script>
<script src="../media/plg_editors_tinymce/js/tinymce.min.js" ></script>
<script>

    function tinyInit(id) {
        tinymce.init({
            selector: 'textarea#basic-'+id,
            width:500,
            height: 120,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code wordcount'
            ],
            toolbar: '',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });
    }

    tinyInit('tn')

    tinyInit('tw1');



    function add_faq() {

        var add_div = document.getElementById("add_div"),
            addFAQBtn = document.getElementById("add_elm"),
            faqWrap = document.createElement("div"),
            clickCount = document.getElementsByClassName("tw").length;

        var mydiv = '<div><label for="faqtitle'+ clickCount +'"> 问题'+clickCount +'：</label><input id="faqtitle'+ clickCount +'" type="text"></div><div><label for="basic-tw'+ clickCount +'">答案'+clickCount +'：</label><textarea class="tw" id="basic-tw'+ clickCount +'"></textarea></div>';
        faqWrap.innerHTML = mydiv;
        add_div.insertBefore(faqWrap, addFAQBtn);
        clickCount++;
        tinyInit('tw'+(clickCount-1));

    }

    function insert_code(code){
        window.parent.Joomla.editors.instances['jform_articletext'].replaceSelection(code);
        window.parent.Joomla.current.close();
    }


    document.addEventListener('DOMContentLoaded', function() {
        "use strict";

        var modulesLinks = document.getElementById('ist');
            modulesLinks.addEventListener('click', function() {

                var body = document.getElementById('body');
                var cbtype = document.getElementById('cbtype');
                var cbcss = document.getElementById('cbcss');

                //var twurl = document.getElementById('twurl');
                var twtype = document.getElementById('twtype');
                var twcontent = document.getElementById('twcontent');

                var cur = document.getElementsByClassName('cur');
                var cur_li = cur.item(0).getAttribute('data-name');

                var iframe = document.getElementById("basic-tn_ifr");
                var content1 = iframe.contentWindow.document.body.innerHTML;
                var twLen =  document.getElementsByClassName("tw").length;

                if(content1 != '' && cur_li=='li_ctb'){

                    insert_code('[contentbox type="'+cbtype.value+'" style="'+cbcss.value+'"]'+content1+'[/contentbox]\n' + '')
                }

                if(twtype.value !='' && cur_li=='li_twt'){

                    insert_code('[twitter type="'+twtype.value+'"]'+twcontent.value+'[/twitter]\n' + '')
                }

                if(cur_li=='li_faq'){
                    var codeStr =  ''; var i = 1;

                    while (i<twLen){
                        console.log("i:"+i);
                        var faqTitle = document.getElementById("faqtitle"+i).value;
                        console.log("i1:"+i);
                        var faqContent = document.getElementById("basic-tw"+i+"_ifr").contentWindow.document.body.innerHTML;
                        console.log(faqTitle);
                        console.log(faqContent);

                        if(faqTitle=='') {
                            i++;
                            continue;
                        }

                        codeStr +='[faq title="'+faqTitle+'"]'+faqContent+'[/faq]\n';
                        i++;
                    }

                    insert_code(codeStr)

                }



            });



    });

</script>

