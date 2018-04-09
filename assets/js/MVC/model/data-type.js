/**
 * @encoding     UTF-8
 * @package      WPObjects
 * @link         https://github.com/VladislavDolgolenko/WPObjects
 * @copyright    Copyright (C) 2018 Vladislav Dolgolenko
 * @license      MIT License
 * @author       Vladislav Dolgolenko <vladislavdolgolenko.com>
 * @support      <help@vladislavdolgolenko.com>
 */


(function(MSP, Backbone){
    
    MSP.ModelDataType = Backbone.Model.extend({
        
        urlRoot: MSP.rest_url + '/data_type',
        
        getCollection: function () {
            if (this.Collection === undefined) {
                var url = this.getModelsUrl();
                this.Collection = new Backbone.Collection(null, {model: this.getModelClass()});
                this.Collection.url = url;
            }
            
            return this.Collection;
        },
        
        getModelClass: function () {
            if (!this.has('id_attr_name')) {
                throw new Error('Undefined id_attr_name in data type model! Maybe model not sync with server.');
            }
            
            var url = this.getModelsUrl();
            var attr = this.get('id_attr_name');
            
            return Backbone.Model.extend({
                idAttribute: attr,
                urlRoot: url,
                validate: this.getModelValidateFunction()
            });
        },
        
        getModelValidateFunction: function () {
            var Fields = this.get('form_fields') ? this.get('form_fields') : [];
            
            return function (attrs, options) {
                for (var i = 0; i < Fields.length; i++) {
                    var Field = Fields[i];
                    if (Field.require === false) {
                        continue;
                    }

                    var attr_id = Field.id === this.idAttribute && this.isNew() ? '_creation_id' : Field.id;

                    if (attrs[attr_id] === undefined || !attrs[attr_id]) {
                        return Field.label + ' is required attribute!';
                    }
                }
                
                var attr_id = this.isNew() ? '_creation_id' : this.idAttribute;
                var id = attrs[attr_id];
                var id_regex = /^[a-zA-Z]{1}[a-zA-Z0-9_]{0,16}$/;
                if (id_regex.test(id) === false) {
                    return 'System id must be has length between 1-17 charts and use only chars, numbers and _.';
                }
            };
        },
        
        getModelsUrl: function () {
            return MSP.rest_url + '/' + this.id;
        },
        
        getMainQualifierDataType: function () {
            if (this.MainQualifierDataType !== undefined) {
                return this.MainQualifierDataType;
            }
            
            var qualifiers = this.get('qualifiers');
            if (!qualifiers || !qualifiers.length) {
                return false;
            }
            
            this.MainQualifierDataType = new MSP.ModelDataType({id: qualifiers[0]});
            
            return this.MainQualifierDataType;
        },
        
        getMainQualifierAttr: function () {
            var qualifier = this.getMainQualifierDataType().id;
            var params = this.get('qualifiers_attr_names');
            if (params !== undefined && params[qualifier] !== undefined) {
                return params[qualifier];
            } else {
                return '_' + qualifier + '_id';
            }
        },
        
    });
    
})(MSP, Backbone);