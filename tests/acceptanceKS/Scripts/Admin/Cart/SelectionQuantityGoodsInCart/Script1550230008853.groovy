 /**
 * @description Изменение кол-ва товара добавляемых в корзину с детальной
 * @step Переходим на лицевую часть сайта
 * @step Проверяем работу кнопоу увеличения и уменьшения товара
 * @step Заполняем поле количества товара
 * @step Нажатие на кнопку "Купить" у первого товара
 * @step Наживаем Перейти в корзину
 * @step Проверка количества товара у первого в списке товара
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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

CatalogData DataCatalog = new CatalogData()

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), 'value')

println(count)

if (count != '1') {
    throw new StepErrorException('Изначальное кол-во товаров не равно 1!')
}

price = WebUI.getText(findTestObject('Object Repository/CMS/Cart/CountGoods/span_price_in_cart'))

println(price)

if (price != DataCatalog.price) {
    throw new StepErrorException('Цена не совпадает с количеством!')
}

WebUI.click(findTestObject('Object Repository/CMS/Cart/CountGoods/div_plus_count_goods_cart'))

WebUI.delay(5)

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), 'value')

println(count)

if (count != '2') {
    throw new StepErrorException('Не увелисивается кол-во товара!')
}

price = WebUI.getText(findTestObject('Object Repository/CMS/Cart/CountGoods/span_price_in_cart'))

println(price)

if (price != DataCatalog.pricex2) {
    throw new StepErrorException('Цена не совпадает с количеством!')
}

WebUI.click(findTestObject('Object Repository/CMS/Cart/CountGoods/div_minus_count_goods_cart'))

WebUI.delay(5)

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), 'value')

println(count)

if (count != '1') {
    throw new StepErrorException('Не уменьшается кол-во товара!')
}

price = WebUI.getText(findTestObject('Object Repository/CMS/Cart/CountGoods/span_price_in_cart'))

println(price)

if (price != DataCatalog.price) {
    throw new StepErrorException('Цена не совпадает с количеством!')
}
WebUI.sendKeys(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), Keys.chord(Keys.BACK_SPACE))
WebUI.sendKeys(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'),  DataCatalog.count)
WebUI.sendKeys(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), Keys.chord(Keys.ENTER))
//WebUI.setText(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), DataCatalog.countint)

WebUI.delay(5)

WebUI.takeScreenshot()

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), 'value')

WebUI.takeScreenshot()

println(count)

if (count != DataCatalog.count) {
    throw new StepErrorException('Количество товара несовпадает!')
}

price = WebUI.getText(findTestObject('Object Repository/CMS/Cart/CountGoods/span_price_in_cart'))

println(price)

if (price != DataCatalog.pricex10) {
    throw new StepErrorException('Цена не совпадает с количеством!')
}

