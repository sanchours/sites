/**
 * @description Кнопка купить
 * @step Переход в каталожный раздел
 * @step Отключение кнопки "Купить" у товара
 * @step Переход на страницу каталога
 * @step Проверка того, что кнопка отсутствует
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import data.GoodsData as GoodsData

WebDriver driver = DriverFactory.getWebDriver()

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_top_menu'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_group_goods'))

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_section_catalog'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_tab_catalog'))

if (WebUI.verifyElementChecked(findTestObject('CMS/Goods/ButtonBuy/checkbox_buy'), 0, FailureHandling.OPTIONAL) ==
	false) {
		WebUI.click(findTestObject('CMS/Goods/ButtonBuy/checkbox_buy'))
	}

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_editor'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

if (WebUI.verifyElementNotPresent(findTestObject('CMS/Goods/ButtonBuy/button_buy_on_page'), 1)) {}  else {}//Такая конструкция для отображения логов в админке в случае присутсвия элемента

