<?php
ini_set('display_errors', 1);
/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 */
return [

    // Supported values: '7.0', '7.1', '7.2', '7.3', null.
    // If this is set to null,
    // then Phan assumes the PHP version which is closest to the minor version
    // of the php executable used to execute phan.
    "target_php_version" => '7.1',

    // A list of directories that should be parsed for class and
    // method information. After excluding the directories
    // defined in exclude_analysis_directory_list, the remaining
    // files will be statically analyzed for errors.
    //
    // Thus, both first-party and third-party code being used by
    // your application should be included in this list.
    'directory_list' => [
        'src',
        'vendor/doctrine/orm/lib/Doctrine/ORM/'
    ],

    // A directory list that defines files that will be excluded
    // from static analysis, but whose class and method
    // information should be included.
    //
    // Generally, you'll want to include the directories for
    // third-party code (such as "vendor/") in this list.
    //
    // n.b.: If you'd like to parse but not analyze 3rd
    //       party code, directories containing that code
    //       should be added to the `directory_list` as
    //       to `exclude_analysis_directory_list`.
    "exclude_analysis_directory_list" => [
        'vendor/',
        'src/DoctrineDatatable/Tests'
    ],

    // A list of plugin files to execute.
    // See https://github.com/phan/phan/tree/master/.phan/plugins for even more.
    // (Pass these in as relative paths.
    // Base names without extensions such as 'AlwaysReturnPlugin'
    // can be used to refer to a plugin that is bundled with Phan)
    'plugins' => [
        // checks if a function, closure or method unconditionally returns.

        // can also be written as 'vendor/phan/phan/.phan/plugins/AlwaysReturnPlugin.php'
        'AlwaysReturnPlugin',

        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        //'HasPHPDocPlugin',
        'InvalidVariableIssetPlugin',
        'InvokePHPNativeSyntaxCheckPlugin',
        'NoAssertPlugin',
        'NonBoolBranchPlugin',
        'NonBoolInLogicalArithPlugin',
        'NumericalComparisonPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
        'SleepCheckerPlugin',
        'UnknownElementTypePlugin',
        'UnreachableCodePlugin',
        'UnusedSuppressionPlugin'
    ],
];