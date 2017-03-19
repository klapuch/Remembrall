var gulp = require('gulp');

module.exports = function (config) {
	gulp.task('fonts', function () {
		gulp.src(['fonts/*'])
			.pipe(gulp.dest(config.fonts));
	});
};