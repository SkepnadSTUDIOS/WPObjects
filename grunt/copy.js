/**
 * @encoding     UTF-8
 * @package      WPObjects
 * @link         https://github.com/VladislavDolgolenko/WPObjects
 * @copyright    Copyright (C) 2018 Vladislav Dolgolenko
 * @license      MIT License
 * @author       Vladislav Dolgolenko <vladislavdolgolenko.com>
 * @support      <help@vladislavdolgolenko.com>
 */


module.exports = function (grunt) { 
    
    var namespace = grunt.option('namespace');
    if (!namespace) {
        throw new Error("Undefined namespace, please use --namespace= when start grunt!");
    }
    
    var dest = grunt.option('dest');
    if (!dest) {
        dest = './build/WPObjects';
    }
    
    return {
        
        build_namespace: {
            
            expand: true,
            cwd: './', 
            src: [
                './AjaxController/**/*',
                './AssetsManager/**/*',
                './Data/**/*',
                './EventManager/**/*',
                './Factory/**/*',
                './GoogleFonts/**/*',
                './Customizer/**/*',
                './FileSystem/**/*',
                './LessCompiler/**/*',
                './Loader/**/*',
                './Session/**/*',
                './Settings/**/*',
                './Log/**/*',
                './Model/**/*',
                './Notice/**/*',
                './Page/**/*',
                './PostType/**/*',
                './Service/**/*',
                './View/**/*',
                './WPModel/**/*',
                './WPFactory/**/*',
                './UI/**/*',
                './VC/**/*',
                './config/**/*',
                './assets/**/*',
                './storage/**/*',
                './license.txt'
            ], 
            dest: dest,
            
            options: {
                processContentExclude: ['**/*.{png,gif,jpg,ico,psd,woff,woff2,ttf}'],
                
                process: function (content, srcpath) {
                    var content_replacement = content;
                    
                    JSregex = /\.js/g;
                    PHPregex = /\.php/g;
                    CSSregex = /\.css/g;
                    Templatesregex = /template/g;
                    
                    if (Templatesregex.exec(srcpath) !== null) {
                        
                        // Html classes
                        var needed = namespace.toLowerCase() + '-';
                        var replacer = "msp-";
                        content_replacement = content_replacement.replace( new RegExp(replacer, "g"), needed);
                        
                    } else if (PHPregex.exec(srcpath) !== null) {
                        
                        // PHP namespaces
                        var needed = namespace + '\\WPObjects\\';
                        var replacer = "WPObjects\\\\";
                        content_replacement = content_replacement.replace( new RegExp(replacer, "gi"), needed);
                        
                    } else if (JSregex.exec(srcpath) !== null) {
                        
                        // JS global varialbe
                        var needed = namespace;
                        var replacer = "MSP";
                        content_replacement = content_replacement.replace( new RegExp(replacer, "g"), needed);
                        
                        // Html classes
                        var needed = namespace.toLowerCase() + '-';
                        var replacer = "msp-";
                        content_replacement = content_replacement.replace( new RegExp(replacer, "g"), needed);
                    
                    } else if (CSSregex.exec(srcpath) !== null) {
                        
                        var needed = '.' + namespace.toLowerCase() + '-';
                        var replacer = ".msp-";
                        content_replacement = content_replacement.replace( new RegExp(replacer, "g"), needed);
                        
                    } else {
                        return content;
                    }
                    
                    return content_replacement;
                }
            }
            
        }
        
    };
    
};