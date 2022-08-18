/**
 * @description Галочки "Хит" "Новина" "Акция"
 * @step Переход в каталожный раздел
 * @step Установка галочек "Хит" "Новина" "Акция"
 * @step Переход на страницу с товаром
 * @step Проверка наличия ярлыков "Хит" "Новина" "Акция"
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

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_section'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_top_menu'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_group_goods'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_section_catalog'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_tab_catalog'))

nameGoods = WebUI.getText(findTestObject('CMS/Goods/CheckboxActivityGoods/div_name_goods'))

WebUI.click(findTestObject('CMS/Goods/CheckboxHitNewSale/checkbox_sale'))

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Goods/CheckboxHitNewSale/checkbox_hit'))

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Goods/CheckboxHitNewSale/checkbox_new'))

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_editor'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementVisible(findTestObject('CMS/Goods/CheckboxHitNewSale/div_hit'))

WebUI.verifyElementVisible(findTestObject('CMS/Goods/CheckboxHitNewSale/div_sale'))

WebUI.verifyElementVisible(findTestObject('CMS/Goods/CheckboxHitNewSale/div_new'))

