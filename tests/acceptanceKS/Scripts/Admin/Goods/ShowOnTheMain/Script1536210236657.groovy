import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
/**
 * @description Вывод товара на главную
 * @step Переход на детальную товара
 * @step Установка галочки "Выводить на главную"
 * @step Сохранение 
 * @step Переход на главную
 * @step Проверка наличия товра 
 */

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
import urlSite.BaseLink as BaseLink

nameGoods = WebUI.getText(findTestObject('CMS/Goods/ShowOnTheMain/div_goods_detail'))

WebUI.doubleClick(findTestObject('CMS/Goods/ShowOnTheMain/div_goods_detail'))

if (WebUI.verifyElementChecked(findTestObject('CMS/Goods/ShowOnTheMain/checkbox_activity'), 3, FailureHandling.OPTIONAL) == 
false) {
    WebUI.click(findTestObject('CMS/Goods/ShowOnTheMain/checkbox_activity'))

    WebUI.click(findTestObject('CMS/Goods/ShowOnTheMain/button_save'))
} else {
    WebUI.click(findTestObject('CMS/Goods/ShowOnTheMain/button_save'))
}

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.verifyTextPresent(nameGoods, false)

