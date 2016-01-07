var gulp = require('gulp');
var less = require('gulp-less');
var path = require('path');
var imagemin = require('gulp-imagemin');
var pngquant = require('imagemin-pngquant');
var watch = require('gulp-watch');
var batch = require('gulp-batch');
var concat = require('gulp-concat');
var _ = require('lodash');
var mainBowerFiles = require('main-bower-files');
var gulpFilter = require('gulp-filter');


var paths = {
  less: './web/less/**/main.less',
  bower: './bower_components/'
};

var bower_js = [
  'angular/angular.min.js'
];

gulp.task('less', function() {
  return gulp.src('./web/less/**/main.less')
    .pipe(less({
      paths: [ path.join(__dirname, 'less', 'includes') ]
    }))
    .pipe(gulp.dest('./web/css'));
});

gulp.task('images', function(){
  return gulp.src('./web/bundles/**/images/**/*.+(jpg|JPG|png|PNG)')
    .pipe(imagemin({
      progressive: true,
      //use: [pngquant()]
    }))
    .pipe(gulp.dest('./web/images'));
});

gulp.task('copyimg', function(){
  return gulp.src('./web/bundles/**/images/**/*.+(gif|GIF|svg|SVG)')
    .pipe(gulp.dest('./web/images'));
});

gulp.task('bower-js', function(){
  var jsfilter = gulpFilter(['*.js']);

  return gulp.src(mainBowerFiles())
    .pipe(jsfilter)
    .pipe(concat('components.js'))
    .pipe(gulp.dest('./web/libs/'));
});

gulp.task('watch', function(){
  gulp.watch(paths.less, ['less']);
});