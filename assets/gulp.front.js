var gulp = require('gulp');
var cleanCss = require('gulp-clean-css');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var order = require('gulp-order');

module.exports = function (config) {
	gulp.task('css', function () {
		gulp.src([
			'front/css/bootstrap.min.css',
			'front/css/main.css',
			'front/css/default_highlight.min.css'
		])
			.pipe(cleanCss({compatibility: '*'}))
			.pipe(concat('front.css'))
			.pipe(gulp.dest(config.directory));
	});

	gulp.task('js', function () {
		gulp.src([
			'front/js/jquery.min.js',
			'front/js/bootstrap.min.js',
			'front/js/highlight.min.js',
			'front/js/main.js'
		])
			.pipe(order([
				'jquery.min.js',
				'bootstrap.min.js',
				'highlight.min.js',
				'main.js'
			]))
			.pipe(uglify())
			.pipe(concat('front.js'))
			.pipe(gulp.dest(config.directory));
	});
};
