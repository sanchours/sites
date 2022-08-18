/**
 * @description Вывод фильтра на странице
 * @step Переход в модуль "Каталог"
 * @step Переход  в "Редактор карточек"
 * @step Переход в детальную карточки
 * @step Переход на детальную поля
 * @step Установка галочки "Использовать в фильтре"
 * @step Сохранение
 * @step Переход в "Настройки вывода товарных позиций"
 * @step Подключение использования фильтра
 * @step Преход в кталожный раздел
 * @step Переход в редактор областей
 * @step Подключение панели фильтра
 * @step Переход во вкладку редактор
 * @step Переход на страницу каталога
 * @step Проверка наличия панели фильтра на странице 
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
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

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_section'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_top_menu'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_group_goods'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_section_catalog'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_region_editor'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_filter_for_catalog'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_editor'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementVisible(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_filter_on_page'))

