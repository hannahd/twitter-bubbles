// @pjs preload must be used to preload the image
/* @pjs preload="profile.jpg"; */
/* @pjs preload="mask.jpg"; */

ArrayList <Bubble> circles = new ArrayList <PVector> ();
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

void setup() {
  size(1280, 720);
  smooth();

  img = loadImage("profile.jpg");
  maskImg = loadImage("mask.jpg");
  imageMode(CENTER);
  
  int i = 0;
  //Generate data
  for (; i<(.02*num_bubbles); i++) {
    data[i] = 8*base_diameter;
  }
  for (; i<(.25*num_bubbles); i++) {
    data[i] = int(random(3,5))*base_diameter;
  }
  for (; i<(.5*num_bubbles); i++) {
    data[i] = int(random(1,3))*base_diameter;
  }
  for (; i<num_bubbles; i++) {
    data[i] = base_diameter;
  }
  
  //Sort data
  sort(data);

  for (int i=0; i<data.length; i++) {
    addBubble(data[i]);
  }

}
 
void draw() {
  if(first){
	img.resize(500, 500);
	maskImg.resize(500, 500);
	img.mask(maskImg);
	first=false;
  }
  background(background_color);
  for (int i=0; i<circles.size(); i++) {
    Bubble b = circles.get(i);
    
    fill(bubble_fill, map(b.opacity, 0, 1, 0, 255));
	strokeWeight(stroke_weight);
    stroke(stroke_color, map(b.opacity, 0, 1, 0, 255));
    if (dist(b.x, b.y, mouseX, mouseY) < b.d/2){
        fill(hover_fill);
        stroke(hover_fill);
        
        ellipse(b.x, b.y, b.d, b.d);
		
		if(b.d > base_diameter){
          image(img, b.x, b.y, b.d-10, b.d-10);
        }
    }else{
      // draw bubble	
        ellipse(b.x, b.y, b.d, b.d);
    }
  }
}

void addBubble(float d) {
  float x = random(width);
  float y = random(height);
  int tries = 10000;
  while (checkOverlap(x, y, d, overlap_edges) && tries > 0) {
    x = random(width);
    y = random(height);
    tries--;
  }
  if (tries > 0) {
    circles.add(new Bubble(x, y, d, 0, 0, random(.4,1)));
  }else{
    hidden++;
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
  for (Bubble b : circles) {
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
  float opacity = 1;
  
  int user_id = 0;  //id of the user who wrote this tweet
  int tweet_id = 0; //id of this tweet
  
  boolean placed = false; //whether or not the bubble fits on the canvas
  
  Bubble(float x_in, float y_in, float d_in, int user_id_in, int tweet_id_in, float o_in) {
    x = x_in; 
    y = y_in; 
    d = d_in;
    user_id = user_id_in;
    tweet_id = tweet_id_in;
    placed = true;
    opacity = o_in;
  }
}

