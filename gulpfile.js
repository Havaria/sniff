'use strict';

var gulp       = require('gulp'),
    sass       = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify     = require('gulp-uglify'),
    rename     = require('gulp-rename'),
    csso = require('gulp-csso'),
    watch = require('gulp-watch'),
    del = require('del'),
    exec = require('child_process').exec;

// SCSS compiled
gulp.task('sass', function () {
    return gulp.src('_dev/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(csso())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('assets/tmp/css/'))
        .pipe(sourcemaps.write('./'))
});

gulp.task('clean', function(){
    return del('core/cache/**', {force:true});
});
gulp.task('build', function(done) {
   exec('gitify build');
   done();
});
gulp.task('extract', function(done){
    exec('gitify extract');
    done();
})
// JS COMPRESSED
gulp.task('compressed-js', function (done) {
    gulp.src('_dev/*.js')
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('assets/tmp/js'))
});

gulp.task('watch', function () {
    gulp.watch(('_dev/*.scss'), gulp.series('sass'));
    gulp.watch(['_data/**/*.tpl','_data/**/*.html'], gulp.series('clean'));
    gulp.watch(['_data/**/*.tpl','_data/**/*.html'], gulp.series('build'));
});

gulp.task('compressed-js:watch', function () {
    gulp.watch(('_dev/*.js'), gulp.series('compressed-js'));
});

