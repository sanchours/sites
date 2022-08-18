/**
 * Подвал cms
 */

Ext.define('Ext.Cms.Footer',{
    extend: 'Ext.panel.Panel',
    region: 'south',
    baseCls: 'b-footer-panel',
    sectionId: 0,
    isLoaded: false,
    isValid: false,
    dataLoaded: false,
    autoScroll: true,
    margin: '3 0 0 0',
    border: 0,
    layout: 'fit'
});
