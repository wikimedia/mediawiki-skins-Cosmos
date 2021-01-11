/* eslint-env node */

module.exports = function ( grunt ) {
	var config = grunt.file.readJSON( 'skin.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		banana: config.MessagesDirs,
		eslint: {
			options: {
				extensions: [ '.js' ],
				cache: true
			},
			all: [
				'**/*.js',
				'!node_modules/**',
				'!vendor/**'
			],
			fix: {
				options: {
					fix: true
				},
				src: [
					'<%= eslint.all %>'
				]
			}
		},
		stylelint: {
			all: [
				'**/*.css',
				'**/*.less',
				'!node_modules/**',
				'!vendor/**'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'banana', 'eslint', 'stylelint' ] );
	grunt.registerTask( 'fix', 'eslint:fix' );
	grunt.registerTask( 'default', 'test' );
};
