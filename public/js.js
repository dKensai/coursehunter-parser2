
// localization: ru, en
// ru by default
const LANG = 'en';


$.getJSON( "../data/local-front.json", function( data ) {
  for( var i in data[LANG] ){
    window[i] = data[LANG][i];
  }
  $('form').find('.download_text').text(paste_link).end()
  		   .find('input[name=url]').attr('placeholder',link_of_course).end()
  		   .find('button').text(download_btn_text);
  $('.jumbotron').find('h3').text(has_loaded);
  parserForm();
}) 


function parserForm(){

 $('form').on('submit',function(e){

     e.preventDefault();

     var $dt = $('form').find('.download_text');
     var $di = $('form').find('.download_img').children('img');

     $dt.fadeOut(function(){
            
           $dt.html(is_loading);
           $dt.fadeIn()
            
     });
     
     $di.fadeIn();

     $('.jumbotron').fadeOut();


       $.ajax({
          type: "POST",
          url: "index.php",
          data: $('form').serialize(),
          beforeSend: function(){},
          success: function(data){
            console.log('data: ',data);

            if( data[0] === true ){

                $dt.fadeOut(function(){
                      
                     $dt.html(paste_link);
                     $dt.fadeIn()
                      
               });

               $di.fadeOut();

                $('.jumbotron').find('p:eq(0)').html(data[1]['title']);

                $('.jumbotron').find('p:eq(1)').html(data[1]['info']);

                $('.jumbotron').find('p:eq(2)').html(data[1]['samples']);
                
                var li = '';

                var videos = data[1]['videos'];

                for(var i in videos){
                    
                    li += '<li>' + videos[i] + '</li>'

                };

                $('.jumbotron').find('ul').html(li);


               $('.jumbotron').fadeIn();

               $('input[name=url]').val('');

            }else{
              
              alert(server_error);

            }

          },
          error: function(error){
            console.log(error);
              console.log(error.responseText);
          },
          complete: function(data){},
      }); 

 })


}

