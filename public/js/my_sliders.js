var $slider1 = $('#slider1');
var $slider2 = $('#slider2');

axios.defaults.headers.common['Authorization'] = 'Bearer ' + document.head.querySelector('meta[name="token"]').content; // @todo Do better
axios.get(route('api.entry_images'))
    .then(res => {
    	var images = res.data;

		if (images.length > 3) 
		{
			
			var firstHalfPictures = images.slice( 0 , images.length/2 );
			var latterHalfPictures = images.slice( images.length/2 , images.length );

			// @todo Somehow the sliders aren't displaying
			$slider1.slider
			({
				title: "pictures ...",
				fade: 500,
				pictures: firstHalfPictures
			});		

			$slider2.slider
			({
				title: "... and more",
				fade: 350,
				pictures: latterHalfPictures
			});	
		}	
    })
    .catch(err => {
        console.error(err);
    });
