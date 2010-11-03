<?php
// $Id$

/**
 * @file
 * Documents API functions for Libraries module.
 */

/**
 * Return information about external libraries.
 *
 * @return
 *   An associative array whose keys are internal names of libraries and whose
 *   values are describing each library. Each key is the directory name below
 *   the 'libraries' directory, in which the library may be found. Each value is
 *   an associative array containing:
 *   - name: The official, human-readable name of the library.
 *   - vendor url: The URL of the homepage of the library.
 *   - download url: The URL of a web page on which the library can be obtained.
 *   - path: (optional) A relative path from the directory of the library to the
 *     actual library. Only required if the extracted download package contains
 *     the actual library files in a sub-directory.
 *   - library path: (optional) The absolute path to the library directory. This
 *     should not be declared normally, as it is automatically detected, to
 *     allow for multiple possible library locations. A valid use-case is an
 *     external library, in which case the full URL to the library should be
 *     specified here.
 *   - version callback: (optional) The name of a function that detects and
 *     returns the full version string of the library. The first argument is
 *     always $library, an array containing all library information as described
 *     here. There are two ways to declare the version callback's additional
 *     arguments, either as a single $options parameter or as multiple
 *     parameters, which correspond to the two ways to specify the argument
 *     values (see 'version arguments'). Defaults to libraries_get_version().
 *   - version: (optional) The version of the library. This should not be
 *     declared normally, as it is automatically detected (see 'version
 *     callback' below) to allow for version changes of libraries without code
 *     changes of implementing modules and to support different versions of a
 *     library simultaneously (though only one version can be installed per
 *     site). A valid use-case is an external library whose version cannot be
 *     determined programatically.
 *   - version arguments: A list of arguments to pass to the version callback.
 *     Version arguments can be declared either as an associative array whose
 *     keys are the argument names or as an indexed array without specifying
 *     keys. If declared as an associative array, the arguments get passed to
 *     the version callback as a single $options parameter whose keys are the
 *     argument names (i.e. $options is identical to the specified array). If
 *     declared as an indexed array, the array values get passed to the version
 *     callback as seperate arguments in the order they were declared. The
 *     default version callback libraries_get_version() expects a single,
 *     associative array with named keys:
 *     - file: The filename to parse for the version, relative to the library
 *       path. For example: 'docs/changelog.txt'.
 *     - pattern: A string containing a regular expression (PCRE) to match the
 *       library version. For example: '/@version (\d+)\.(\d+)/'.
 *     - lines: (optional) The maximum number of lines to search the pattern in.
 *       Defaults to 20.
 *     - cols: (optional) The maximum number of characters per line to take into
 *       account. Defaults to 200. In case of minified or compressed files, this
 *       prevents reading the entire file into memory.
 *   - files: An associative array of library files to load. Supported keys are:
 *     - js: A list of JavaScript files to load, using the same syntax as Drupal
 *       core's hook_library().
 *     - css: A list of CSS files to load, using the same syntax as Drupal
 *       core's hook_library().
 *     - php: A list of PHP files to load.
 *   - variants: (optional) An associative array of available library variants.
 *     For example, the top-level 'files' property may refer to a default
 *     variant that is compressed. If the library also ships with a minified and
 *     uncompressed/source variant, those can be defined here. Each key should
 *     describe the variant type, e.g. 'minified' or 'source'. Each value is an
 *     associative array of top-level properties that are entirely overridden by
 *     the variant, most often just 'files'. Additionally, each variant can
 *     contain following properties:
 *     - variant callback: (optional) The name of a function that detects
 *       returns TRUE or FALSE, depending on whether the variant is available or
 *       not. The first argument is always $library, an array containing all
 *       library information as described here. The second is always a string
 *       containing the variant name. There are two ways to declare the variant
 *       callback's additinal arguments, either as a single $options parameter
 *       or as multiple parameters, which correspond to the two ways to specify
 *       the argument values (see 'variant arguments'). If ommitted, the variant
 *       is expected to always be available.
 *     - variant arguments: A list of arguments to pass to the variant callback.
 *       Variant arguments can be declared either as an associative array whose
 *       keys are the argument names or as an indexed array without specifying
 *       keys. If declared as an associative array, the arguments get passed to
 *       the variant callback as a single $options parameter whose keys are the
 *       argument names (i.e. $options is identical to the specified array). If
 *       declared as an indexed array, the array values get passed to the
 *       variant callback as seperate arguments in the order they were declared.
 *     Variants can be version specific.
 *   - versions: (optional) An associative array of supported library versions.
 *     Naturally, external libraries evolve over time and so do their APIs. In
 *     case a library changes between versions, different 'files' may need to be
 *     loaded, different 'variants' may become available, or Drupal modules need
 *     to load different integration files adapted to the new version.
 *     Each key is a version *string* (PHP does not support floats as keys).
 *     Each value is an associative array of top-level properties that are
 *     entirely overridden by the version.
 *   - integration files: (optional) An associative array whose keys are module
 *     names and whose values are sets of files to load for the module, using
 *     the same notion as the top-level 'files' property. Each specified file
 *     should contain the full path to the file.
 *   Additional top-level properties can be registered as needed.
 *
 * @see hook_library()
 */
function hook_libraries_info() {
  // The following is a full explanation of all properties. See below for more
  // concrete example implementations.

  // This array key lets Libraries API search for 'sites/all/libraries/example'
  // directory, which should contain the entire, original extracted library.
  $libraries['example'] = array(
    // Only used in administrative UI of Libraries API.
    'title' => 'Example library',
    'vendor url' => 'http://example.com',
    'download url' => 'http://example.com/download',
    // Optional: If, after extraction, the actual library files are contained in
    // 'sites/all/libraries/example/lib', specify the relative path here.
    'path' => 'lib',
    // Optional: Define a custom version detection callback, if required.
    'version callback' => 'mymodule_get_version',
    // Specify arguments for the version callback. By default,
    // libraries_get_version() takes a named argument array:
    'version arguments' => array(
      'file' => 'docs/CHANGELOG.txt',
      'pattern' => '/@version (\d+)\.(\d+)/',
      'lines' => 5,
      'cols' => 20,
    ),
    // Default list of files of the library to load. Important: Only specify
    // third-party files belonging to the library here, not integration files of
    // your module.
    'files' => array(
      // 'js' and 'css' follow the syntax of hook_library(), but file paths are
      // relative to the library path.
      'js' => array(
        'exlib.js',
        'gadgets/foo.js',
      ),
      'css' => array(
        'lib_style.css',
        'skin/example.css',
      ),
      // For PHP libraries, specify include files here, still relative to the
      // library path.
      'php' => array(
        'exlib.php',
        'exlib.inc',
      ),
    ),
    // Optional: Specify alternative variants of the library, if available.
    'variants' => array(
      // All properties defined for 'minified' override top-level properties.
      'minified' => array(
        'files' => array(
          'js' => array(
            'exlib.min.js',
            'gadgets/foo.min.js',
          ),
          'css' => array(
            'lib_style.css',
            'skin/example.css',
          ),
        ),
        'variant callback' => 'mymodule_check_variant',
        'variant arguments' => array(
          'variant' => 'minified',
        ),
      ),
    ),
    // Optional, but usually required: Override top-level properties for later
    // versions of the library. The properties of the minimum version that is
    // matched override the top-level properties. Note:
    // - When registering 'versions', it usually does not make sense to register
    //   'files', 'variants', and 'integration files' on the top-level, as most
    //   of those likely need to be different per version and there are no
    //   defaults.
    // - The array keys have to be strings, as PHP does not support floats for
    //   array keys.
    'versions' => array(
      '2' => array(
        'files' => array(
          'js' => array(
            'exlib.js',
          ),
          'css' => array(
            'exlib_style.css',
          ),
        ),
      ),
      '3.0' => array(
        'files' => array(
          'js' => array(
            'exlib.js',
          ),
          'css' => array(
            'lib_style.css',
          ),
        ),
      ),
      '3.2' => array(
        'files' => array(
          'js' => array(
            'exlib.js',
            'gadgets/foo.js',
          ),
          'css' => array(
            'lib_style.css',
            'skin/example.css',
          ),
        ),
      ),
    ),
    // Optional: Register files to auto-load for your module. All files must be
    // keyed by module, and follow the syntax of the 'files' property.
    'integration files' => array(
      'mymodule' => array(
        'js' => 'ex_lib.inc',
      ),
    ),
  );

  // A very simple library. No changing APIs (hence, no versions), no variants.
  // Expected to be extracted into 'sites/all/libraries/simple'.
  $libraries['simple'] = array(
    'title' => 'Simple library',
    'vendor url' => 'http://example.com/simple',
    'download url' => 'http://example.com/simple',
    'version arguments' => array(
      'file' => 'readme.txt',
      // Best practice: Document the actual version strings for later reference.
      // 1.x: Version 1.0
      'pattern' => '/Version (\d+)\.(\d+)/',
      'lines' => 5,
    ),
    'files' => array(
      'js' => array(
        'simple.js',
      ),
    ),
  );

  // A library that (naturally) evolves over time with API changes.
  $libraries['tinymce'] = array(
    'title' => 'TinyMCE',
    'vendor url' => 'http://tinymce.moxiecode.com',
    'download url' => 'http://tinymce.moxiecode.com/download.php',
    'path' => 'jscripts/tiny_mce',
    'version arguments' => array(
      // It can be easier to parse the first characters of a minified file
      // instead of doing a multi-line pattern matching in a source file. See
      // 'lines' and 'cols' below.
      'file' => 'jscripts/tiny_mce/tiny_mce.js',
      // Best practice: Document the actual version strings for later reference.
      // 2.x: this.majorVersion="2";this.minorVersion="1.3"
      // 3.x: majorVersion:'3',minorVersion:'2.0.1'
      'pattern' => '@majorVersion[=:]["\'](\d).+?minorVersion[=:]["\']([\d\.]+)@',
      'lines' => 1,
      'cols' => 100,
    ),
    'versions' => array(
      '2.1' => array(
        'files' => array(
          'js' => array('tiny_mce.js'),
        ),
        'variants' => array(
          'source' => array(
            'files' => array(
              'js' => array('tiny_mce_src.js'),
            ),
          ),
        ),
        'integration files' => array(
          'wysiwyg' => array(
            'js' => array(
              drupal_get_path('module', 'wysiwyg') . '/editors/js/tinymce-2.js',
            ),
            'css' => array(
              drupal_get_path('module', 'wysiwyg') . '/editors/js/tinymce-2.css',
            ),
          ),
        ),
      ),
      // Definition used if 3.1 or above is detected.
      '3.1' => array(
        // Does not support JS aggregation.
        'files' => array(
          'js' => array(
            'tiny_mce.js' => array('preprocess' => FALSE),
          ),
        ),
        'variants' => array(
          // New variant leveraging jQuery. Not stable yet; therefore not the
          // default variant.
          'jquery' => array(
            'files' => array(
              'js' => array(
                'tiny_mce_jquery.js' => array('preprocess' => FALSE),
              ),
            ),
          ),
          'source' => array(
            'files' => array(
              'js' => array(
                'tiny_mce_src.js' => array('preprocess' => FALSE),
              ),
            ),
          ),
        ),
        'integration files' => array(
          'wysiwyg' => array(
            'js' => array(
              drupal_get_path('module', 'wysiwyg') . '/editors/js/tinymce-3.js',
            ),
            'css' => array(
              drupal_get_path('module', 'wysiwyg') . '/editors/js/tinymce-3.css',
            ),
          ),
        ),
      ),
    ),
  );
  return $libraries;
}

/**
 * Alter the library information before detection and caching takes place.
 *
 * The library definitions are passed by reference. A common use-case is adding
 * a module's integration files to the library array, so that the files are
 * loaded whenever the library is. As noted above, it is important to declare
 * integration files inside of an array, whose key is the module name.
 *
 * @see hook_libraries_info()
 */
function hook_libraries_info_alter(&$libraries) {
  $files = array(
    'php' => array('example_module.php_spellchecker.inc'),
  );
  $libraries['php_spellchecker']['integration files']['example_module'] = $files;
}

/**
 * Specify paths to look for library info files.
 *
 * Libraries API looks in the following directories for library info files by
 * default:
 * - libraries
 * - profiles/$profile/libraries
 * - sites/all/libraries
 * - sites/$site/libraries
 * This hook allows you to specify additional locations to look for library info
 * files. This should only be used for modules that declare many libraries.
 * Modules that only implement a few libraries should implement
 * hook_libraries_info().
 *
 * @return
 *   An array of paths.
 */
function hook_libraries_paths() {
  // Taken from the Libraries test module, which needs to specify the path to
  // the test library.
  return array(drupal_get_path('module', 'libraries_test') . '/example');
}
