<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.sessiongc
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\MetadataManager;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
/**
 * Garbage collection handler for session related data
 *
 * @since  3.8.6
 */
class PlgSystemAnalyzelogic extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  3.8.6
     */
    protected $app;

    /**
     * Database driver
     *
     * @var    JDatabaseDriver
     * @since  3.8.6
     */
    protected $db;

    /**
     * Runs after the HTTP response has been sent to the client and performs garbage collection tasks
     *
     * @return  void
     *
     * @since   3.8.6
     */

    public function onContentPrepare($context, &$article, &$params, $page){

        $link_title[0] = isset($article->readmore_link)?$article->readmore_link:'';
        $link_title[0] = substr($link_title[0],1);
        $link_title[1] = isset($article->title)?$article->title:'';

        $link_title[2] = isset($article->id)?$article->id:'';
        $link_title[3] = isset($article->catid)?$article->catid:'';
        $link_title[4] = isset($article->lang)?$article->lang:'';
        $link_title[5] = isset($article->alias)?$article->alias:'';


        //var_dump($this->get_efs_url($link_title[2],$link_title[3],$link_title[4]));exit;


        //  $link_url = ContentHelperRoute::getArticleRoute( $article->id, $catid, $lang);


        //将旧的recommend转换为短代码
        $this->old_recommend_to_shortcode($article);

        $this->old_twitter_to_shortcode($article,'<a class="article-inner-twitter-btn','<a class="article-inner-twitter+btn');
        $this->old_twitter_to_shortcode($article,'<a class="article-inner-twitter-boxbtn','<a class="article-inner-twitter+boxbtn');
        //加载配置文件中的短代码
        foreach (include "config.php" as $shortcode){

            //存在短代码时，执行对应的短代码处理函数
            if(strpos( $article->text,'['.$shortcode)!==false &&
                strpos( $article->text,'[/'.$shortcode.']')!==false){

                //计算短代码出现次数
                $count = substr_count( $article->text,'['.$shortcode);$i = 0;
                while ($i<$count){
                    //必须存在名为$shortcode的函数
                    $article->text = $this->$shortcode($article->text,$shortcode,$link_title);$i++;
                }
            }
        }
    }

    //根据id等参数获取文章url
    public function get_efs_url($id,$catid,$lang='*'){
        //include_once JPATH_ROOT . '/components/com_content/helpers/route.php';
        $url = ContentHelperRoute::getArticleRoute( $id, $catid, $lang);
        $res = substr(juri::root(), 0, -1) . JRoute::link('site', $url);
        return $res;
    }


    //处理推荐文章短代码
    public  function  recommend($text = null,$code='',$link_title){

        $findl = strpos($text,'['.$code);
        $findr = strpos($text,'[/'.$code.']');

        $shortcode = substr($text,$findl,$findr-$findl+strlen('[/'.$code.']'));

        if(!isset(explode('"',$shortcode)[1]))return $text;
        $alis = explode('"',$shortcode)[1];

        $lang = JFactory::getLanguage();
        $curre_lang = $lang->get('tag');
        $default_lang = $lang->getDefault();

        $lang_arr = [$curre_lang,$default_lang,'*'];//降级语言组

        $res = $this->getArticles($alis);
        $res_data = '';
        if(!$res||!isset($res[0])){
            $text = str_replace($shortcode,'',$text);
            return $text;
        }

        foreach ($lang_arr as $v){
            $res_data = $this->getResArray($res,$v);
            if($res_data!=false){
                break;
            }
        }

        if(!$res_data)
            $res_data = $res[0];

        $imgs = json_decode($res_data['images']);

        $img_url = JURI::base().$imgs->image_intro;

        if($imgs->image_intro!=''){
            $img_info = getimagesize($img_url);
        }else{
            $img_info ='';
        }

        $uri = Uri::getInstance();
        $amp = $uri->getQuery();

        if($amp=='amp'){
            $img_url = $imgs->image_intro;
        }

        $text = $this->str_replace_limit($shortcode,include ("code/recommend.php"),$text,1);

        return  $text;
    }
    //根据语种返回对应数组
    public function getResArray($arr,$lang){
        foreach ($arr as $v){
            if($v['language'] == $lang){
                return $v;
            }
        }
        return false;
    }

    //获取文章信息
    public function getArticles($alis){
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->select($this->db->quoteName('catid'))
            ->select($this->db->quoteName('language'))
            ->select($this->db->quoteName('title'))
            ->select($this->db->quoteName('images'))
            ->select($this->db->quoteName('metadesc'))
            ->from($this->db->quoteName('#__content'))
            ->where($this->db->quoteName('alias') . ' = ' . "'".$alis."'");
        $this->db->setQuery($query);

        $res = $this->db->loadAssocList();

        return $res;
    }

    //处理外部推荐文章短代码
    public  function  extrecommend($text = null,$code='',$link_title){

        //return $text;
        $findl = strpos($text,'['.$code);
        $findr = strpos($text,'[/'.$code.']');

        $shortcode = substr($text,$findl,$findr-$findl+strlen('[/'.$code.']'));

        if(!isset(explode('"',$shortcode)[1]))return $text;

        $arr = explode('"',$shortcode);

        $url = $arr[1];
        $title = $arr[3];
        $desc = $arr[5];

        $alias_slice = explode('/',$url);
        $alias = $alias_slice[count($alias_slice)-1];
        $alias = substr($alias,0,-5);
        $langList = JLanguageHelper::getContentLanguages();

        $url_info = parse_url($url);

        if(isset($url_info['path'])){
            $lang = explode('/',$url_info['path'])[1];
            $lang_copy = $lang;
        }else{
            $text = str_replace($shortcode,'',$text);
            return $text;
        }

        $is_in_lang_list = $this->has_min_lang($lang);

        if($is_in_lang_list){
            foreach ($langList as $v){
                if($v->sef==$lang){
                    $lang = $v->lang_code;
                    break;
                }
            }
        }else{
            $lang = '';
        }

        $post['option'] = 'com_ajax';
        $post['plugin'] = 'minitoolajax';
        $post['format'] = 'raw';
        $post['alias'] = $alias;
        $post['lang'] = $lang;
        $post['sign'] = $this->get_sign($post);

        $url_profix = $url_info['scheme'].'://'.$url_info['host'].'/';

        $send_url = $url_profix.'index.php';

       $out_data =  $this->send_post($send_url,$post);

        $out_data = json_decode($out_data);

      // echo "<pre>"; var_dump($out_data->data[0]->url);exit;

       if(empty($out_data->data) || !isset($out_data->data[0]->images)||!isset($out_data->data[0]->url)) {
           $text = str_replace($shortcode,'',$text);
           return $text;
       }

        $out_url = $out_data->data[0]->url;

        $img_url =  $url_profix.$out_data->data[0]->images;

        //var_dump($img_url);exit;

        $title=$title!=''?$title:$out_data->data[0]->title;
        $desc=$desc!=''?$desc:$out_data->data[0]->metadesc;



        //var_dump($text);exit;
        $text = $this->str_replace_limit($shortcode,include ("code/extrecommend.php"),$text,1);

        return  $text;
    }

    public function has_min_lang($lang){
        return in_array($lang,['de','en','es','fr','it','ja','jp','nl','pt','tr','zh']);

    }

    //处理文本框短代码
    public function contentbox($text = null,$code='',$link_title){
        $findl = strpos($text,'['.$code);
        $findr = strpos($text,'[/'.$code.']');

        $shortcode = substr($text,$findl,$findr-$findl+strlen('[/'.$code.']'));

        $content = substr($shortcode,strpos($shortcode,']')+1,-strlen('[/'.$code.']'));

        $exps = explode('"',$shortcode);

        $type = $exps[1];
        $style = $exps[3]!='none'?$exps[3]:'';

        switch ($type){
            case "tip":
                $upper = JText::_("TPL_MINITOOL_TIPS");break;
            case "note":
                $upper = JText::_("TPL_MINITOOL_NOTE");break;
            case "warn":
                $upper = JText::_("TPL_MINITOOL_WARNING");break;
            default:
                $upper = "none";

        }

        $div1 = $type!='none'?$type:'';
        $div2 = $type!='none'?'icon-awe-'.$type:'';
        $div3 = $type!='none'?'<strong>'.$upper.': </strong>':'';

        $all_div2 = $div2!=''?'<div class="article-inner-content-icon '.$div2.'"></div>':'';
        $div1 = $div1!=''? ' '.$div1.' '.$style:' noicon '.$style;


        $text = $this->str_replace_limit($shortcode,include ("code/contentbox.php"),$text,1);

        return $text;
    }

    //处理twitter短代码
    public function twitter($text = null,$code='',$link_title){
        $findl = strpos($text,'['.$code);
        $findr = strpos($text,'[/'.$code.']');

        $shortcode = substr($text,$findl,$findr-$findl+strlen('[/'.$code.']'));

        $content = substr($shortcode,strpos($shortcode,']')+1,-strlen('[/'.$code.']'));


        $exps = explode('"',$shortcode);


        if(!isset($exps[1]))return $text;
        $type = $exps[1];

        $style = $type=='text'?'boxbtn':'btn';

        $url_text = $style=='btn'?$link_title[1]:$content;

        $url_in = $this->get_efs_url($link_title[2],$link_title[3],$link_title[4]);
        //JUri::base().urlencode($link_title[0])

        //不存在url则用生成url，存在则使用旧的old_url
        if(isset($exps[3])){
            $url = $exps[3];
        }else{
            $url = "https://twitter.com/intent/tweet?url=".urlencode($url_in) . "&text=" . urlencode($url_text) . "&via=MiniTool_";
        }

        $content_str = $type=='text'?'<span class="article-inner-twitter-boxbtn-text">'.$content.'</span><span class="article-click-to-twitter icon-awe-twitter">'.JText::_("TPL_MINITOOL_CLICK_TO_TWEET").'</span>':'<span class="icon-awe-twitter">'.JText::_("TPL_MINITOOL_CLICK_TO_TWEET").'</span>';

        $text = $this->str_replace_limit($shortcode,include ("code/twitter.php"),$text,1);

       // var_dump($url_in);exit;

        return $text;
    }

    //处理faq短代码
    public function faq($text = null,$code='',$link_title){
        $findl = strpos($text,'['.$code);
        $findr = strpos($text,'[/'.$code.']');

        $shortcode = substr($text,$findl,$findr-$findl+strlen('[/'.$code.']'));
        $q = explode('"',$shortcode)[1];
        $a = substr($shortcode,strpos($shortcode,']')+1,-strlen('[/'.$code.']'));

        $text = $this->str_replace_limit($shortcode,include ("code/faq.php"),$text,1);

        return $text;

    }

    //将旧的recommend转换为短代码
    public function old_recommend_to_shortcode($obj){
        $rec_div = '<div class="article-recommend">';

        $fake_div = '<div class="article+recommend">';

        $find_text = $obj->text;

        $indexs = [];//首尾下标

       // var_dump(strpos($find_text,$rec_div));

        while (is_numeric(strpos($find_text,$rec_div))){
            $f_div_index = strpos($find_text,$rec_div);

            $e_div_index = $this->find_end_tag_index($find_text,$f_div_index,'<div','</div>');
            $indexs[] = [$f_div_index,$e_div_index];

            $find_text = $this->str_replace_limit($rec_div,$fake_div,$find_text,1);
        }

        $len = count($indexs);

        for ($i=$len-1;$i>-1;$i--){
            $slice = substr($obj->text,$indexs[$i][0],$indexs[$i][1]);

            $params = parse_url($this->getUrlByAtag($slice));
            $new_url = JRoute::link('site', $this->getUrlByAtag($slice));
            $vars = explode('/',$new_url);
            $alias = substr(end($vars),0,-5);

            if(!isset($params['host'])){

                $obj->text = str_replace($slice,'[recommend link="'.$alias.'"][/recommend]',$obj->text);

            }else{
                if($this->isWhiteList($params['host'])){

                    $obj->text = str_replace($slice,'[extrecommend link="'.$this->getUrlByAtag($slice).'" title="" desc=""][/extrecommend]',$obj->text);
                }
            }
        }

    }

    public function isWhiteList($host){
       $list =  ['www.minitool.com','de.minitool.com','jp.minitool.com','www.minitool.fr','www.partitionwizard.com','www.partitionwizard.jp','youtubedownload.minitool.com','moviemaker.minitool.com','videoconvert.minitool.com','mt-3.com'];
        return in_array($host,$list);
    }

    //抓取a标签中的链接
    public function getUrlByAtag($str){
        $find_l = "<a";
        $find_r = ">";
        $l_index = strpos($str,$find_l);
        $sub_r = substr($str,$l_index);

        $r_index = strpos($sub_r,$find_r);
        $res_str = substr($sub_r,0,$r_index);
        return $this->getUrlByHref($res_str);

    }

    //抓取href中的内容
    public function getUrlByHref($sub_str){
        $sign = '"';
        $l_index = strpos($sub_str,'href='.$sign);

        if($l_index===false){
            $sign = "'";
            $l_index = strpos($sub_str,'href='.$sign);
        }

        $tmp_str = substr($sub_str,$l_index+6);
        $r_index = strpos($tmp_str,$sign);
        $res_str = substr($sub_str,$l_index+6,$r_index);

        return $res_str;
    }

    //将旧的twitter转换为短代码
    public function old_twitter_to_shortcode($obj,$rec_div,$fake_div){

        $find_text = $obj->text;

        $indexs = [];//首尾下标

        while (strpos($find_text,$rec_div)){
            $f_div_index = strpos($find_text,$rec_div);
            $e_div_index = $this->find_end_tag_index($find_text,$f_div_index,'<a','</a>');
            $indexs[] = [$f_div_index,$e_div_index];
            $find_text = $this->str_replace_limit($rec_div,$fake_div,$find_text,1);
        }
        // var_dump(substr($find_text,952,30));
        $len = count($indexs);

        $type = explode('+',$fake_div)[1];

        $style = $type=='btn'?'basic':'text';
        $btntext = '';

        for ($i=$len-1;$i>-1;$i--){
            $slice = substr($obj->text,$indexs[$i][0],$indexs[$i][1]);
            if(!isset(explode("'",$slice)[1])) return;
            $url = explode("'",$slice)[1];
            if($style == 'text'){
                $btntext =  substr(strip_tags($slice),0,-14);
            }

            $obj->text = str_replace($slice,'[twitter  type="'.$style.'" url="'.$url.'"]'.$btntext.'[/twitter]',$obj->text);
        }

    }


    //计算结尾标签下标
    public function find_end_tag_index($text,$f_div_index,$lt,$rt){
        $len = strlen($text) - $f_div_index;

        for ($i=0;$i<$len;$i++){
            $slice = substr($text,$f_div_index,$i);
            $lt_count = substr_count($slice,$lt);
            $rt_count = substr_count($slice,$rt);

            if($lt_count!=0 && $rt_count!=0 && $rt_count==$lt_count){

                return $i;
            }
        }
    }

    //重写替换方法，实现控制替换次数
    function str_replace_limit($search, $replace, $subject, $limit=-1) {

        if (is_array($search)) {
            foreach ($search as $k=>$v) {
                $search[$k] = '`' . preg_quote($search[$k],'`') . '`';
            }
        }
        else {
            $search = '`' . preg_quote($search,'`') . '`';
        }

        return preg_replace($search, $replace, $subject, $limit);
    }

    //发送post请求
    function send_post($url, $data){

       //var_dump($url);exit;

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

       // var_dump($result);exit;
        if ($result == FALSE || $result=='' || empty($result)) {

            return json_encode(['code'=>-1,'msg'=>'接口请求失败','data'=>[]]);

        }
        return $result;

    }


    //生成密钥
    private function get_sign($data){
        date_default_timezone_set('Asia/Shanghai');

        $sign = md5($data['option'].'#'.$data['plugin'].'#'.$data['format'].'#'.getdate()['hours']);

        return $sign;
    }



}
