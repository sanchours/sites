/**
 * @description Отзывы в товарах
 * @step Переход в модуль "Каталог"
 * @step Переход в настройки
 * @step Снятие галочки "Отызвы в товарах"
 * @step Сохранение
 * @step Переход на детальную товара
 * @step Проверка отсутствия таба "Отзывы" на детальной 
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

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_settings'))

if (WebUI.verifyElementAttributeValue(findTestObject('CMS/Goods/ReviewInGoods/checkbox_review_in_goods'), 'aria-checked', 'false', 1, FailureHandling.OPTIONAL)){
	
		WebUI.click(findTestObject('CMS/Goods/ReviewInGoods/checkbox_review_in_goods'))
	}

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_save_settings'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_section'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_top_menu'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_group_goods'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_section_catalog'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_editor'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('CMS/Goods/ChecboxShowDetail/div_detail_on page'))

WebUI.verifyElementPresent(findTestObject('CMS/Goods/ReviewInGoods/div_tab_review'), 0,FailureHandling.STOP_ON_FAILURE)

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_settings'))

WebUI.click(findTestObject('Object Repository/CMS/Goods/ReviewInGoods/input_reviev_goods_rez'))

WebUI.click(findTestObject('CMS/Goods/ReviewInGoods/checkbox_review_in_goods'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_save_settings'))

WebUI.switchToWindowIndex(1)

WebUI.refresh()

if (WebUI.verifyElementNotPresent(findTestObject('CMS/Goods/ReviewInGoods/div_tab_review'), 0)) {}  else {}//Такая конструкция для отображения логов в админке в случае присутсвия элемента