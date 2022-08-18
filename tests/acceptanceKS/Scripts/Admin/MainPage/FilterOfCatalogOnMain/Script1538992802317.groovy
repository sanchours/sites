/**
 * @description Подключение фильтра к странице
 * @step Подключение использования фильра на странице
 * @step Включение параметрического поиска
 * @step Подключения поля для использования в карточке
 * @step Создание раздела 
 * @step Включение 
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

WebUI.click(findTestObject('CMS/Goods/span_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_card_editor'))

WebUI.doubleClick(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_base_card_detail'))

WebUI.doubleClick(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_field_in_card'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/checkbox_use_in_filter'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/button_save'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_settings_goods'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_panel_filter_list'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_panel_filter_list_item'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/button_save'))

WebUI.click(findTestObject('CMS/Form/DeleteForm/span_ok'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_catalog_settings'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_parametr_search'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/button_save_settings'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_section'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_top_menu'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_editor'))

attribute = WebUI.getAttribute(findTestObject('CMS/Tree/frame-sktree'), 'class')

if (attribute.contains('collapsed')) {
    WebUI.click(findTestObject('CMS/Tree/btn_header_tree'))
}

WebUI.click(findTestObject('CMS/Tree/NewSection/add_section'))

WebUI.verifyElementPresent(findTestObject('CMS/Tree/NewSection/add_frame'), 30)

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_title'), 'Test Form')

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_alias'), 'test-form')

WebUI.click(findTestObject('CMS/Tree/NewSection/change_parent_section'))

WebUI.click(findTestObject('CMS/Tree/NewSection/li_top_menu'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_type_section'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/li_catalog'))

WebUI.click(findTestObject('CMS/Tree/NewSection/save'))

GlobalVariable.id = WebUI.getText(findTestObject('CMS/MainPage/LinksAllarticles/div_id_articles_section'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_tab_catalog'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_type_section_in_tab'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/li_type_section_item'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/button_save_section_type'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_list_cart_of_goods'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/li_cart_of_goods_item'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/button_save_config'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/button_save_view_config'))

