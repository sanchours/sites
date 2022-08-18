/**
 * @description Редактирование товара
 * @step Переход в каталог
 * @step Переход в список товаров
 * @step Переход На детальную товара
 * @step Редактировение полей
 * @step Сохранение
 * @step Проверка того, что значения изменились
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

Name = 'EditingName'

Article = '123456'

Price = '321'

WebUI.click(findTestObject('CMS/Goods/span_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_goods'))

WebUI.getText(findTestObject('CMS/Goods/EditingGoods/div_article'))

WebUI.getText(findTestObject('CMS/Goods/EditingGoods/div_price'))

WebUI.getText(findTestObject('CMS/Goods/EditingGoods/div_name_goods'))

WebUI.doubleClick(findTestObject('CMS/Goods/EditingGoods/div_name_goods'))

WebUI.setText(findTestObject('CMS/Goods/EditingGoods/input_goods_name'), Name)

WebUI.setText(findTestObject('CMS/Goods/EditingGoods/input_goods_article'), Article)

WebUI.setText(findTestObject('CMS/Goods/EditingGoods/input_goods_price'), Price)

WebUI.click(findTestObject('CMS/Goods/EditingGoods/button_save'))

WebUI.verifyElementText(findTestObject('CMS/Goods/EditingGoods/div_name_goods'), Name)

WebUI.verifyElementText(findTestObject('CMS/Goods/EditingGoods/div_article'), Article)

WebUI.verifyElementText(findTestObject('CMS/Goods/EditingGoods/div_price'), Price)

