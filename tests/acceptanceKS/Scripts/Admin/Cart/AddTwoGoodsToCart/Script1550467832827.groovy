/**
 * @description Добавления двух товаров в корзину
 * @step Переходим на лицевую часть сайта
 * @step Нажатие на кнопку "Купить" у первого товара
 * @step Наживаем Продолжить покупки
 * @step Нажатие на кнопку "Купить" у второго товара
 * @step Наживаем Перейти в корзину
 * @step Проверка названия у первого и второго товара
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.Keys as Keys
import data.CatalogData as CatalogData
import urlSite.BaseLink as BaseLink

CatalogData DataCatalog = new CatalogData()

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/a_catalog_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_buy_first_in_list'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/a_continue_shopping'))

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Cart/DeleteItem/button_buy_second_in_list'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_road_to_cart'))

Title = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/div_first_title_cart'))

if (Title != DataCatalog.itemname) {
	throw new StepErrorException('Название товара неверено!')
}

Title2 = WebUI.getText(findTestObject('Object Repository/CMS/Cart/DeleteItem/div_second_title_cart'))

if (Title2 != DataCatalog.itemnamesec) {
	throw new StepErrorException('Название второго товара неверено!')
}