ArrayList <PVector> circles = new ArrayList <PVector> ();
float diameter = 100;

int num_bubbles = 350;

int base_diameter = 20;
int[] data = new int[num_bubbles];

void setup() {
  size(1280, 720);
  colorMode(HSB, 360, 100, 100);
  noStroke();
  smooth();
  
int i = 0;
  //Generate data
  for (; i<(.01*num_bubbles); i++) {
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

  for (int i=200; i>0; i--) {
    addCircle(i);
  }
}
 
void draw() {
  background(0);
  //addCircle();
  for (int i=0; i<circles.size(); i++) {
    PVector p = circles.get(i);
    fill(255,255,255);
    if ( dist(p.x, p.y, mouseX, mouseY) < (p.z/2) ) fill(0,0,0,15);
    strokeWeight(1);
    stroke(30);
    
    ellipse(p.x, p.y, p.z, p.z);
  }
}

void addCircle(int d) {
  PVector c = randomPlacement(d);
  int tries = 10000;
  while (overlap(c) && tries > 0) {
    c = randomPlacement(d);
    tries--;
  }
  if (!overlap(c)) {
    circles.add(c);
  }
}

 
PVector randomPlacement(int d) {
  return new PVector(random(width), random(height), d);
}
 
boolean overlap(PVector c) {
  for (PVector p : circles) {
    if (dist(c.x, c.y, p.x, p.y) < (c.z + p.z)*0.5+5) {
      return true;
    }
  }
  return false;
}

