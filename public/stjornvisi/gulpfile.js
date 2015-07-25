var gulp = require('gulp');
var csslint = require('gulp-csslint');
var vulcanize = require('gulp-vulcanize');
var plumber = require('gulp-plumber');

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

gulp.task('vulcanize', function () {
    return gulp.src('components/app.html')
        .pipe(vulcanize({
            excludes: [],
            inlineScripts: true,
            inlineCss: true,
            stripExcludes: true
        }))
        .pipe(gulp.dest('./webcomponents'));
});

gulp.task('watch', function () {
    gulp.watch('components/**/*html', ['vulcanize']);
});
