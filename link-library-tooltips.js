/* This is the JavaScript used for the Link Library plugin to show the link description 
 * as a tooltip when hovering over the link. 
 *
 * In Library Settings → Advanced, the Link Name should be wrapped in the following:
 * <span onmouseover='tooltip.show(this.parentNode)' onmouseout='tooltip.hide();'> </span> 
 *
 * Link Description should be wrapped in the following:
 * <div style='display:none' id='tooltip'> </div>
 *
 * Place link-library-tooltips.js in the Child Theme directory and then use the following in
 * the functions.php file to load the JavaScript:
 
	 function raamdev_link_library_tooltips() {
		global $post;
		if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'link-library') ) {
			wp_enqueue_script( 'link-library-tooltips', get_stylesheet_directory_uri() . '/link-library-tooltips.js', array(), '1.0.0', true );
		}
	 }
	 add_action( 'wp_enqueue_scripts', 'raamdev_link_library_tooltips');
 *
 * See also the custom stylesheet (should replace everything inside Link Library → Stylesheet):
 * https://gist.github.com/raamdev/25eaf22080955154dc5385187712b3d7
 */
var tooltip=function(){
	var id = 'tt';
	var top = 3;
	var left = 3;
	var maxw = 300;
	var speed = 10;
	var timer = 20;
	var endalpha = 95;
	var link;
	var alpha = 0;
	var tt,t,c,b,h;
	var ie = document.all ? true : false;
	return{
		show:function(v,w){
			if(tt == null){
				tt = document.createElement('div');
				tt.setAttribute('id',id);
				c = document.createElement('div');
				c.setAttribute('id',id + 'cont');
				tt.appendChild(c);
				document.body.appendChild(tt);
				tt.style.opacity = 0;
				tt.style.filter = 'alpha(opacity=0)';
				document.onmousemove = this.pos;
			}
			tt.style.display = 'block';

			link = v.getElementsByTagName('a')[0];
			link._title = link.title;
			link.title = '';

			tt.innerHTML = v.getElementsByTagName('div')[0].innerHTML;

			tt.style.width = w ? w + 'px' : 'auto';
			if(!w && ie){
				tt.style.width = tt.offsetWidth;
			}
			if(tt.offsetWidth > maxw){tt.style.width = maxw + 'px'}
			h = parseInt(tt.offsetHeight) + top;
			clearInterval(tt.timer);
			tt.timer = setInterval(function(){tooltip.fade(1)},timer);
		},
		pos:function(e){
			var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
			var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
			tt.style.top = (u - h) + 'px';
			tt.style.left = (l + left) + 'px';
		},
		fade:function(d){
			var a = alpha;
			if((a != endalpha && d == 1) || (a != 0 && d == -1)){
				var i = speed;
				if(endalpha - a < speed && d == 1){
					i = endalpha - a;
				}else if(alpha < speed && d == -1){
					i = a;
				}
				alpha = a + (i * d);
				tt.style.opacity = alpha * .01;
				tt.style.filter = 'alpha(opacity=' + alpha + ')';
			}else{
				clearInterval(tt.timer);
				if(d == -1){tt.style.display = 'none'}
			}
		},
		hide:function(){
			clearInterval(tt.timer);
			tt.timer = setInterval(function(){tooltip.fade(-1)},timer);
			link.title = link._title;
		}
	};
}();
