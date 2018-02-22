<?php
/**
 * @author Morty zhu
 * @web http://showmeyh.cn/wordpress/
 * Date: 2018/02/22
 * Time: 10:35
 * 爬虫类
 */

class crawler{

    /**
     * 定义爬虫类内部变量
     */

    static $runTime = 300;


    /**
     * 爬取内容
     * @param $url
     * @return bool|string
     */
    protected function _getContentOfUrl($url){
        $handle = fopen($url, "r");
        if($handle){
            $content = stream_get_contents($handle,1024*1024);
            return $content;
        }else{
            return false;
        }

    }


    /**
     * 匹配页面中的url
     * @param $webContent
     * @return mixed
     */
    protected function _filterUrl($webContent){
        //匹配a标签的正则
        $reg_tag_a = '/<[a|A].*?href=[\'\"]{0,1}([^>\'\"\ ]*).*?>/';
        $result = preg_match_all($reg_tag_a,$webContent,$match_result);
        if($result){
            return $match_result[1];
        }
    }


    /**
     * 转换相对路径到绝对路径
     * @param $baseUrl
     * @param $urlList
     * @return array
     */
    protected function _transformUrl($baseUrl,$urlList){
        //转换后的绝对路径
        $absoluteUrlList = array();
        if(is_array($urlList)){
            foreach ($urlList as $urlItem){
                if(preg_match('/^(http:\/\/|https:\/\/|javascript:)/',$urlItem)){
                    //本身为绝对路径
                    $realUrl = $urlItem;
                }else{
                    if(preg_match('/^\//',$urlItem)){
                        #分层级的相对路径
                        $realUrl = $baseUrl.$urlItem;
                    }else{
                        #同一水平位置的文件路径
                        $realUrl = $baseUrl.'/'.$urlItem;
                    }
                }

                $absoluteUrlList[] = $realUrl;
            }
        }

        return $absoluteUrlList;

    }

    /**
     * 去除非本网站网址
     * @param $jd_url_list
     * @param $url_base
     * @return array|bool
     */
    protected function _filterOther($jd_url_list,$url_base){
        $all_url_list = array();
        if(is_array($jd_url_list)){
            foreach($jd_url_list as $all_url){
                echo $all_url;
                if(strpos($all_url,$url_base)===0){
                    $all_url_list[]=$all_url;
                }
            }
            return $all_url_list;
        }else{
            return false;
        }

    }

    /**
     * 爬虫主程序
     * @param $url
     */
    public function justDOit($url){
        $fp_puts = fopen("url.txt","ab");//记录url列表
        $fp_gets = fopen("url.txt","r");//保存url列表
        $fp_cont = fopen("content.txt","ab");
        //先行获取数据
        do{
            $content = $this->_getContentOfUrl($url);
            fputs($fp_cont,$content."\r\n");
            $urlList = $this->_filterUrl($content);

            $urlList = $this->_filterOther($urlList,$url);
            //var_dump($urlList);
            $urlList = $this->_transformUrl($url,$urlList);

            if(is_array($urlList)){
                foreach ($urlList as $urlItem){
                    fputs($fp_puts,$urlItem."\r\n");
                }
            }
        }while($url = fgets($fp_gets,1024));


    }



}