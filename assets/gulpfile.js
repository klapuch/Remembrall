var gulp = require('gulp');
var front = require('./gulp.front.js');
var base = require('./gulp.base.js');

var config = {
	base: {
		fonts: 'dist/fonts'
	},
	front: {
		directory: 'dist/front'
	}
};

base(config.base);
front(config.front);

gulp.task('build', ['css', 'js', 'fonts']);