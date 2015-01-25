var gulp = require('gulp');
var csslint = require('gulp-csslint');

gulp.task('css', function() {
    gulp.src('stylesheets/*.css')
        .pipe(csslint({
            'adjoining-classes': false,
            'universal-selector': false,
            'star-property-hack': false, //Compass uses famous star hack
            'box-sizing': false
        }))
        .pipe(csslint.reporter());
});