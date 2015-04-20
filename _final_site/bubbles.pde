// @pjs preload must be used to preload the image
/* @pjs preload="img/mask.jpg"; 
   @pjs font="Helvetica.ttf";
*/

ArrayList <Bubble> bubbles = new ArrayList <Bubble> ();
float diameter = 100;
float padding = 2; // amount of padding in between bubbles
int num_bubbles = 400;
int base_diameter = 20;
boolean overlap_edges = false;
int[] data = new int[num_bubbles];
int hidden = 0;
boolean first = true;

// appearance
int stroke_weight = 1;
color stroke_color = #238EBD;
color bubble_fill = #5AB8E0;
color background_color = #C6E9F4;
color hover_fill = #ffffff;
color main_color = #2082AD;
color body_text_color = #000000;
color accent_text_color = #224467;
color header_text_color = #FFFFFF;
color faded_text_color = #62A4C4;

int min_time = Date.now();
int max_time = 0;
int time_threshold = 7200; //2 hours

PImage img, maskImg;
Bubble rolled_over = null;
boolean display_profile = false;


void setup() {
  size(1, 1);
  smooth();

  //img = loadImage("profile.jpg");
  maskImg = loadImage("img/mask.jpg");
  imageMode(CENTER);
  
  int i = 0;
  //Generate data
  // for (; i<(.02*num_bubbles); i++) {
  //    data[i] = 8*base_diameter;
  //  }
  //  for (; i<(.25*num_bubbles); i++) {
  //    data[i] = int(random(3,5))*base_diameter;
  //  }
  //  for (; i<(.5*num_bubbles); i++) {
  //    data[i] = int(random(1,3))*base_diameter;
  //  }
  //  for (; i<num_bubbles; i++) {
  //    data[i] = base_diameter;
  //  }
  //  
  //  //Sort data
  //  sort(data);
  // 
  //  for (int i=0; i<data.length; i++) {
  //    addBubble(data[i]);
  //  }

}
 
void draw() {
	cursor(ARROW);
  // if(first){
  //  	maskImg.resize(500, 500);
  //  	first=false;
  //  }
  background(background_color);
  boolean still_over = false;
  for (int i=0; i<bubbles.size(); i++) {
    Bubble b = bubbles.get(i);
    int opacity;
 	opacity = map(b.time, min_time, max_time, 50, 255);
	// if(b.time > Date.now()-time_threshold){
	// 	opacity = 255;
	// }else{ 
	// 	opacity = map(b.time, time_threshold*2, Date.now()-time_threshold, 100, 255);
	// }
    fill(bubble_fill, opacity);
	strokeWeight(stroke_weight);
    stroke(stroke_color, opacity);
    if (dist(b.x, b.y, mouseX, mouseY) < b.d/2){
        rolled_over = b;
		still_over = true;
    }else{
      // draw bubble
		img = loadImage(b.profile_pic);		
		img.mask(maskImg);
		
        ellipse(b.x, b.y, b.d, b.d);
		if(display_profile){
			tint(255, 70);
			if(b.d > base_diameter){
		      image(img, b.x, b.y, b.d-10, b.d-10);
		    }else{
			  image(img, b.x, b.y, b.d, b.d);
			}
		}
    }
  }
  if(still_over){
	drawRollover(rolled_over);
  }else{
	rolled_over = null;
  }
}

void drawRollover(Bubble b){
	cursor(HAND);
	rectMode(CORNER);
	fill(hover_fill);
    stroke(hover_fill);
    
	img = loadImage(b.profile_pic);		
		img.mask(maskImg);
	 	
	    ellipse(b.x, b.y, b.d, b.d);
		
	if(b.d > base_diameter){
		tint(255, 255);      
		image(img, b.x, b.y, b.d-10, b.d-10);
	}
	
	//x,y, w, h, r
	//Calculate x & y
	int tweet_w = 352;
	int tweet_h = 130;
	int tweet_x = b.x + b.d/2 + 10;
	int tweet_y = b.y - tweet_h/2;
	int padding = 15;
	int stats_h = 30;
	
	boolean flip = false;
	// check bottom
    if (tweet_y > height - tweet_h) {
        tweet_y = height - tweet_h - 5;
    }

    // check ceiling
    if (tweet_y < 0) {
        tweet_y = 0;
    }
	
	// check right side
	if(tweet_x > width - tweet_w){
		flip = true;
		tweet_x = b.x - tweet_w - b.d/2 - 10;
	}
	stroke(#e1e7ea);
	rect(tweet_x, tweet_y, tweet_w, tweet_h, 7);
	textSize(11);
	fill(accent_text_color);
		
	textAlign(LEFT,TOP);
	text(b.name + " @" + b.screenname, tweet_x+padding, tweet_y+padding, tweet_w-padding*2, 15);
	textSize(14);
	fill(body_text_color);
	text(b.tweet, tweet_x+padding, tweet_y+padding+ 15, tweet_w-padding*2, tweet_h-stats_h-15-padding*2); 
	stroke(#e1e7ea);
	line(tweet_x, tweet_y+tweet_h-stats_h-padding, tweet_x+tweet_w, tweet_y+tweet_h-stats_h-padding);
	noStroke();
	textSize(9);
	fill(#a6a6a6);
	textAlign(CENTER,CENTER);
	text("average\ntweets\nper day", tweet_x+padding+40, tweet_y+tweet_h-stats_h-padding, 45, stats_h+padding-2); 
	text("retweets", tweet_x+padding+145, tweet_y+tweet_h-stats_h-padding,45, stats_h+padding-2); 
	text("follower to\nfollowing\nratio", tweet_x+padding+270, tweet_y+tweet_h-stats_h-padding, 45, stats_h+padding-2);
	fill(faded_text_color);
	textSize(24);
	textAlign(RIGHT,CENTER);
	float tpd = b.tpd;
	if(tpd >= 1 || tpd < .1){
		tpd = tpd.toFixed(0);
	}else{
		tpd = tpd.toFixed(1);
	}
	float fr = b.fr;
	if(fr >= 1 || fr < .1){
		fr = fr.toFixed(0);
	}else{
		fr = fr.toFixed(1);
	}
	text(tpd, tweet_x+padding, tweet_y+tweet_h-stats_h-padding, 35, stats_h+padding-4); 
	text(b.rt, tweet_x+padding+40+45, tweet_y+tweet_h-stats_h-padding, 57, stats_h+padding-4); 
	text(fr, tweet_x+padding+145+45, tweet_y+tweet_h-stats_h-padding, 74, stats_h+padding-4);
	//text(b.tpd + " " + b.fr + " " + b.rt, tweet_x+padding, tweet_y+tweet_h-stats_h, tweet_w-padding*2, stats_h); 
	      
	if(flip){
		noStroke();
		fill(#ffffff);
		triangle(b.x - b.d/2, b.y, b.x-b.d/2-12, b.y+8, b.x - b.d/2-12, b.y-8);
	}else{
		noStroke();
		fill(#ffffff);
		triangle(b.x + b.d/2, b.y, b.x+b.d/2+12, b.y+8, b.x + b.d/2+12, b.y-8);
	}
	
}

void addBubble(float d, float time, String url, String name, String screenname, String tweet, int id, float tpd, float fr, int rt) {
  float x = random(width);
  float y = random(height);
  int tries = 5000;
  while (checkOverlap(x, y, d, overlap_edges) && tries > 0) {
    x = random(width);
    y = random(height);
    tries--;
  }
  if (tries > 0) {
	Bubble b = new Bubble(x, y, d, time);
	b.profile_pic = url;
	b.name = name;
	b.screenname = screenname;
	b.tweet = tweet;
	b.id = id;
	b.tpd = tpd;
	b.fr = fr;
	b.rt = rt;
    bubbles.add(b);
  }else if (d > base_diameter){
	addBubble(d-base_diameter, time, url, name, screenname, tweet, id, tpd, fr, rt);
  } else{
    hidden++;
  }
}

void mousePressed() { 
 	if(rolled_over != null){
		link("http://twitter.com/"+rolled_over.screenname+"/status/"+rolled_over.id, "_blank"); 
	 }
}
 
boolean checkOverlap(float x, float y, float d, boolean overlapEdges) {
  // check if bubbles are allowed to overlap the edges
  if(!overlapEdges){
    // check bottom
    if (y > height - d/2) {
        return true;
    }
   
    // check ceiling
    if (y < d/2) {
        return true;
    }
   
    // check left border
    if (x < d/2) {
        return true;
    }
   
    // check right border
    if (x > width - d/2) {
       return true;
    }
  }     
  // check bubbles
  for (Bubble b : bubbles) {
    if (dist(x, y, b.x, b.y) < (d + b.d)/2+padding) {
      return true;
    }
  }
  return false;
}



class Bubble {

  float x = 0; //x position on the screen
  float y = 0; //y position on the screen
  
  float d = 0; //diameter
  float time = 0;
  
  String profile_pic = "";
  String name = "";
  String screenname = "";
  String tweet = "";
  int id = -1;
  float tpd = 0;
  float fr = 0;
  float rt = 0;

  boolean placed = false; //whether or not the bubble fits on the canvas
  
  Bubble(float x_in, float y_in, float d_in, float t_in) {
    x = x_in; 
    y = y_in; 
    d = d_in;
    placed = true;
    time = t_in;
  }
}

void createBubbles(int w, int h, Object tweet_arr, boolean show_profile) {
	display_profile = show_profile
    size(w, h);
	bubbles = new ArrayList <Bubble> ();
	for(Object i in tweet_arr){
		 		if(tweet_arr[i].created_time > max_time) max_time = tweet_arr[i].created_time;
		 		if(tweet_arr[i].created_time < min_time) min_time = tweet_arr[i].created_time;
	 			//console.log(tweet_arr[i]);
				int factor;
				
				// quiet tweeters who post a lot
				if(tweet_arr[i].tweets_per_day > 10){
					factor = 1;
					
					if(tweet_arr[i].retweets/tweet_arr[i].follow_ratio > .25){
						factor = 3;
					}else if(tweet_arr[i].retweets/tweet_arr[i].follow_ratio > .5){
						factor = 5;
					}
				}
				else if(tweet_arr[i].tweets_per_day < 1){
					factor = 5;
				}else{
					factor = map(tweet_arr[i].tweets_per_day, 1, 10, 2, 4);
					if(tweet_arr[i].retweets/tweet_arr[i].follow_ratio > .25){
						factor = 3;
					}else if(tweet_arr[i].retweets/tweet_arr[i].follow_ratio > .5){
						factor = 5;
					}
				}
				// Check if it's a retweet
				if(tweet_arr[i].tweet.substring(0, 2) == "RT"){
					factor = factor - 3;
				}
				if(factor == 5){
					factor = 8;
				}
				if(factor < 1){
					factor = 1;
				}
				
		 		addBubble(factor*base_diameter, tweet_arr[i].created_time, tweet_arr[i].profile_img, tweet_arr[i].name, tweet_arr[i].screen_name, tweet_arr[i].tweet, tweet_arr[i].id, tweet_arr[i].tweets_per_day, tweet_arr[i].follow_ratio, tweet_arr[i].retweets);
	}
  	// 	
  	// for (int i=0; i<data.length; i++) {
  	//     		    addBubble(data[i]);
  	//     		}
	console.log(hidden + " tweets were not able to be shown.");
}
