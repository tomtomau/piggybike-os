var navTarget = document.getElementById('nav-target');
var navToggle = document.getElementById('nav-toggle');

if (null !== navToggle && null !== navTarget) {
  navToggle.addEventListener('click', function(){
    navTarget.classList.toggle('active');
  });
}