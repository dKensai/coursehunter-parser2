<?php

namespace App;

use Core\Config;
use Core\Error;

/**
 *   
 *   Parsing from https://coursehunters.net parsing
 *
 *   PHP 7.0
 */

class Parser{

  // course link
	private $url;

  // loaded source
	private $html;

	private $main_info = [
        'rus_name' => '',
        'original_name' => '',
        'producer' => '',
	];

  // messages after loading
	private $messages = [];

  // number of proxy data request
  private $request_index = 0;
  
  // html of proxy page to parse for ip & port
  private $proxy_data;





  /**
   * Constructor.
   * Get source content
   * @param string $url course url
   */
	function __construct($url){

    $this->url = $url;

    $this->localization();

    $this->getProxyData();

	}

   /**
   * Initialize localization variables
   * @return void
   */  
  function localization(){

    $local = file_get_contents('../data/local-back.json');

    $local = json_decode($local,true);

    foreach ($local[Config::LANG] as $k => $v) {
          $this->{$k} = $v;
    }


  }




   /**
   * Get html of proxy addresses page
   * @return void
   */
  function getProxyData(){
    
    $curl = curl_init();
    
    curl_setopt($curl, CURLOPT_URL, "https://free-proxy-list.net/anonymous-proxy.html");

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.3');
                       
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false);

    $this->proxy_data = curl_exec($curl);

    curl_close($curl);
   
  }


   /**
   * Parse html of proxy addresses page & get proxy ip and port to use it in further request
   * @param string $ip_regexp proxy ip regexp pattern
   * @param string $port_regexp proxy port regexp pattern
   * @return array
   */
  function getProxyIPAndPort($ip_regexp,$port_regexp){

    $ip_regexp = '#' . $ip_regexp . '#';

    preg_match($ip_regexp, $this->proxy_data, $ip_info);
    
    $ip_info_length = count($ip_info);
    
    $proxy_ip = $ip_info[$ip_info_length-1];

    $port_regexp = '#' . $port_regexp . '#';

    preg_match($port_regexp, $this->proxy_data, $port_info);
    
    $port_info_length = count($port_info);

    $proxy_port = $port_info[$port_info_length-1];
    
    $proxy_data = [];
    $proxy_data['proxy_ip'] = $proxy_ip;
    $proxy_data['proxy_port'] = $proxy_port;

    return $proxy_data;   

  }




  /**
   * Parser start
   * @return void
   */	
	public function run(){

	   $this->getMainInfo();

	}


  /**
   * Get course main info
   * @return void
   */ 
  private function getMainInfo(){

      $proxy_ip_regexp = 'id="proxylisttable".+<tbody><tr><td>([\d\.]+)';
      $proxy_port_regexp = 'id="proxylisttable".+<tbody><tr><td>[\d\.]+<\/td><td>(\d+)';
        
      if( $this->request_index > 0 ){
        for( $i = 0; $i < $this->request_index; $i++ ){
          $proxy_ip_regexp .= '.*?</tr><tr><td>([\d\.]+)';
          $proxy_port_regexp .= '.*?</tr><tr><td>[\d\.]+<\/td><td>(\d+)';
        }
      }
  
      $proxy_data = $this->getProxyIPAndPort($proxy_ip_regexp,$proxy_port_regexp); 

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url, 
        CURLOPT_RETURNTRANSFER => 1, 
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.3', 
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_PROXY => $proxy_data['proxy_ip'] . ':' . $proxy_data['proxy_port'],
        CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
        CURLOPT_TIMEOUT => 600,
        CURLOPT_CONNECTTIMEOUT => 600,
      ));
        
        
      $html = curl_exec($curl);

      curl_close($curl);

              
      if( $html !== false ) {
        $this->parseMainInfo($html);
        $this->makeMainInfoFile();
        $this->getSampleFiles($html);
        $this->getVideoFiles($html);
      }
      
      if( $html === false ){
        $this->request_index++;
        if( $this->request_index === Config::IP_LIMIT ){
          $this->messages[] = $this->proxy_error;  
          $this->message($this->messages,true,false);    
        }
        $this->getMainInfo();
      }

  }




  /**
   * Parse course main info
   * @param string $html html of course page
   * @return void
   */	
	private function parseMainInfo($html){

			// course name in russian
			preg_match('/<header.+\s*<h1>(.+)?<\/h1>/i', $html, $match);

			$this->main_info['rus_name'] = isset($match[1]) ? $this->sanitize_string($match[1]) : $this->rus_name_error;

			// original name
			preg_match('/class="original-name.+?>(.+)?</i', $html, $match);

			$this->main_info['original_name'] = isset($match[1]) ? $match[1] : '';

			// course link
			preg_match('/class="go-to-publisher.+?href="(.+)?"/i', $html, $match);

			$this->main_info['producer'] = isset($match[1]) ? $match[1] : '';



	}

  /**
   * Create main info file
   * @return void
   */	
	private function makeMainInfoFile(){
        

		$dir = Config::BASE_DIR . $this->main_info['rus_name'];

		if (!is_dir($dir)){

			mkdir($dir);

		} 

		$txt = $this->origin_course_name . ': ' . $this->main_info['original_name'] . "\n\n" .
		       $this->manufacturer . ': ' . $this->main_info['producer'] . "\n\n" . 
		       $this->course_link  . ': ' . $this->url;


        file_put_contents( $dir . "/INFO.txt", $txt);
        
        $this->messages['title'] = $this->main_info['rus_name'];

        $this->messages['info'] = $this->info_file;   
  
	}


  /**
   * Get and save exercises files ( if founded )
   * @param string $html html of course page
   * @return void
   */	
    private function getSampleFiles($html){

    	  $dir = Config::BASE_DIR . $this->main_info['rus_name'];

    		preg_match_all('/<h2>Загрузки<\/h2>\s*<a.*?downloads.*?href="(.+)?"/mui', $html, $match);

    		$zip = isset( $match[1][0] ) ? $match[1][0] : false;

    		if( !$zip ){

    			$this->messages['samples'] = '<span class="text-danger">' . $this->examples_files_no . '</span>'; 

    		}else{

    			$ex = $dir . "/Ex";

    			if (!is_dir($ex)){

    				mkdir($ex);

    			};

    			if ( !file_put_contents($ex . "/files.zip", file_get_contents($zip)) ){

    				$this->messages['samples'] = '<span class="text-danger">' . $this->examples_files_no . '</span>';

    			}else{

    				$this->messages['samples'] = $this->examples_files_found; 

    			}			

    		}

    }


  /**
   * Get and save video files
   * @param string $html html of course page
   * @return void
   */
    private function getVideoFiles($html){

        $dir = Config::BASE_DIR . $this->main_info['rus_name'];

    		preg_match_all('/lessons-list__li.*\s*<.*\s*<.*?itemprop="name">(.+)?<\/span>\s*.*\s*.*\s*.*\s*.*?href="(.+\.mp4)"/mui', $html, $match);

    		$lessons_list_names = isset($match[1]) ? $match[1] : $this->lessons_list_names_error;
    		$lessons_list_videos = isset($match[2]) ? $match[2] : $this->lessons_list_names_error;      

        if( is_array( $lessons_list_names ) ){

            $lessons_list_names_count = count($lessons_list_names);
  
            foreach ($lessons_list_names as $k => $v) {

               $file_name = $this->sanitize_string($lessons_list_names[$k]);

               if( file_put_contents( $dir . "/" . $file_name . ".mp4", file_get_contents($lessons_list_videos[$k])) ){

                $this->messages['videos'][] = $lessons_list_names[$k];

                if( $k === $lessons_list_names_count - 1){
                  $this->message($this->messages,false,true); 
                }

               }else{
                         
                  $this->message($this->file_download_error . ' ' . $lessons_list_videos[$k]);

               }         
               
            }

        }else{

          $this->messages['videos'][] = $this->lessons_videos_error;
          $this->message($this->messages,false,true);

        }

    }

  /**
   * Show messages and die the script
   * @param string $text message text
   * @param boolean $exception Optional write exception if error
   * @param boolean $status Optional message status for JS script
   * @return void
   */
    private function message($text,$exception=true,$status=false){

    	      header('Content-Type: application/json');   

            echo json_encode([ $status, $text ]);

            if( $exception ){

            	 throw new \Exception($text);  

            }

           exit;  
    }


  /**
   * Additional method of lines handling
   * Delete dangeruos symbols for secure files and folders creation
   * @param string $string string that will be name of file or folder
   * @return string
   */
    private function sanitize_string($string){
        
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        $string = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $string);

        return $string;

    }





}