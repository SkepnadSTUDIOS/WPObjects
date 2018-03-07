/**
 * @encoding     UTF-8
 * @package      WPObjects
 * @link         https://github.com/VladislavDolgolenko/WPObjects
 * @copyright    Copyright (C) 2018 Vladislav Dolgolenko
 * @license      MIT License
 * @author       Vladislav Dolgolenko <vladislavdolgolenko.com>
 * @support      <help@vladislavdolgolenko.com>
 */


(function(MSP, Backbone, _, $){
    
    MSP.ViewDashboardFilters = Backbone.View.extend({
        
        template: _.template(MSP.TmplDashboardFilters),
        tagName: 'ul',
        className: 'hor-tabs',
        
        initialize: function () {
            this.FilterDataType = this.model.getMainQualifierDataType();
            if (!this.FilterDataType) {
                this.$el.hide();
                return;
            }
            
            this.FilterDataType.fetch();
            this.listenTo(this.FilterDataType, 'sync', this.initCollection);
        },
        
        initCollection: function () {
            this.Collection = this.FilterDataType.getCollection();
            this.Collection.fetch({reset: true});
            this.listenTo(this.Collection, 'reset', this.render);
        },
        
        render: function () {
            if (this.Collection === undefined) {
                return;
            }
            
            this.$el.html(this.template({Collection: this.Collection}));
        },
        
        events: {
            'click a': 'selectFilter'
        },
        
        selectFilter: function (e) {
            this.$('li').removeClass('active');
            $(e.currentTarget).parents('li').first().addClass('active');
            
            var filter_id = $(e.currentTarget).attr('data-model-id');
            var attr = this.model.getMainQualifierAttr();
            
            var Collection = this.model.getCollection();
            Collection.fetchFilters[attr] = filter_id;
            Collection.fetch({reset: true, data: Collection.fetchFilters});
        }
        
    });
    
})(MSP, Backbone, _, jQuery);