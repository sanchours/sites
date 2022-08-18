/**
 * @description Проверка изменения иконки корзины
 * @step Переходим на лицевую часть сайта
 * @step Проверки что в корзине нет товара
 * @step Нажатие на кнопку "Купить" у второго товара и Продолжить покупки
 * @step Проверяем что у ииконки корзины появился 1 товар с ценой второго продукта
 * @step Увеличиваем кол-во первого товара до 2
 * @step Нажатие на кнопку "Купить" у первого товара и Продолжить покупки
 * @step Проверяем что у ииконки корзины 3 товара с ценой (второй продукт + 2х первый продукт)
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
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW

CatalogData DataCatalog = new CatalogData()

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/a_catalog_page_link'))

WebUI.switchToWindowIndex(1)

emptybasket = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/ins_cart_clear'))

if (emptybasket != DataCatalog.emptybasket) {
	throw new StepErrorException('изначально корзина не пуста!')
}

//WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/span_count_item_icone'), 1)
//
//WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/span_price_item_icone'), 1)

WebUI.click(findTestObject('Object Repository/CMS/Cart/DeleteItem/button_buy_second_in_list'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/a_continue_shopping'))

WebUI.delay(2)

Count = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/span_count_item_icone'))

if (Count != '1') {
	throw new StepErrorException('Кол-во добавленого товара не 1!')
}

Price = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/span_price_item_icone'))

if (Price != DataCatalog.pricesec) {
	throw new StepErrorException('Цена не совпадает с добавленной!')
}

WebUI.click(findTestObject('Object Repository/CMS/Cart/CountGoods/div_plus_count_goods'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_buy_first_in_list'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/a_continue_shopping'))

Count2 = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/span_count_item_icone'))

if (Count2 != '3') {
	throw new StepErrorException('Кол-во добавленых товаров во второй итерации не 2!')
}

Price2 = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/span_price_item_icone'))

if (Price2 != DataCatalog.priceicon) {
	throw new StepErrorException('Цена добавленная во второй итерации не совпадает с ожидаемой!')
}