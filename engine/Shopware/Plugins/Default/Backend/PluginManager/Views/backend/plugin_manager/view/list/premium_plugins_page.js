
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.list.PremiumPluginsPage', {
    extend: 'Shopware.apps.PluginManager.view.list.StoreListingPage',
    alias: 'widget.plugin-manager-premium-plugins-page',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        Shopware.app.Application.on('plugin-reloaded', function(plugin) {
            me.communityStore.each(function(record, index) {
                if (record && record.get('technicalName') == plugin.get('technicalName')) {
                    me.communityStore.remove(record);
                }
            });

            if (plugin.get('id') > 0) {
                plugin.set('groupingState', null);
                plugin.dirty = false;
                try {
                    me.communityStore.add(plugin);
                } catch (e) {
                    me.communityStore.load();
                }
            }

            me.communityStore.sort();
            me.communityStore.group();
            me.hideLoadingMask();
        });
    },

    createStoreListing: function() {
        var me = this;

        var content = me.callParent(arguments);

        me.communityStore.filter({ property: "premium", value: true });
        me.communityStore.load();

        return content;
    },

    createListing: function() {
        var me = this;

        var listing = me.callParent(arguments);

        listing.addItems = function(records) {
            var self = this, plugins = [];

            Ext.each(records, function (record) {
                plugins.push(me.createListItem(record));
            });

            self.listingContainer.add(plugins);
        };


        return listing;
    },

    createFilterPanel: function() {
        return Ext.create('Ext.container.Container', {
            border: false,
            items: [
                Ext.create('Ext.container.Container', {
                    html: '{s name="premium_plugins/headline"}Shopware Premium Plugins - Try for free!{/s}',
                    cls: 'headline',
                    padding: '30 30 0 30'
                }),
                Ext.create('Ext.container.Container', {
                    html: '{s name="premium_plugins/description_text"}Try our premium plugins 30 days free of charge and without obligation.{/s}',
                    padding: '10 30 0 30'
                })
            ]
        });
    },

    createListItem: function(record) {
        var me = this;

        return Ext.create('PluginManager.components.StorePlugin', {
            record: record,
            onClickElement: function(record) {
                var me = this;
                me.displayPluginEvent(record, function(detailWindow) {
                    detailWindow.setActivePriceTab('test');
                });
            },
            createButton: function() {
                var me = this,
                         cls,
                         text,
                         handlerCallback = function() {
                            me.displayPluginEvent(record, function(detailWindow) {
                                detailWindow.setActivePriceTab('test');
                            });
                        };

                switch(true) {
                    case record.allowUpdate():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button update',
                            html: '{s name="update_plugin"}{/s}',
                            handler: function() {
                                me.updatePluginEvent(record);
                            }
                        });

                    case record.allowInstall():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button install',
                            html: '{s name="install"}{/s}',
                            handler: function() {
                                me.registerConfigRequiredEvent(record);
                                me.installPluginEvent(record);
                            }
                        });

                    case record.allowActivate():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button activate',
                            html: '{s name="activate"}{/s}',
                            handler: function() {
                                me.activatePluginEvent(record);
                            }
                        });

                    case record.allowConfigure():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button configure',
                            html: '{s name="configure"}{/s}',
                            handler: handlerCallback
                        });

                    case record.isAdvancedFeature():
                    case record.isLocalPlugin():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button locale',
                            html: '{s name="open"}{/s}',
                            handler: handlerCallback
                        });
                }


                return Ext.create('PluginManager.container.Container', {
                    cls: 'button configure',
                    html: '{s name="premium_plugins/try_button"}Try now{/s}',
                    handler: handlerCallback
                });
            }
        });
    }

});