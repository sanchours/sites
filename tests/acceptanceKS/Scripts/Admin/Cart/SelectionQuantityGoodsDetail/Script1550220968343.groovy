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
import urlSite.BaseLink as BaseLink

CatalogData DataCatalog = new CatalogData()

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/a_catalog_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/a_first_title_goods_catalog_list'))

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods'), 'value')

println(count)

if (count != '1') {
    throw new StepErrorException('Изначальное кол-во товаров не равно 1!')
}

WebUI.click(findTestObject('Object Repository/CMS/Cart/CountGoods/div_plus_count_goods'))

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods'), 'value')

println(count)

if (count != '2') {
    throw new StepErrorException('Не увелисивается кол-во товара!')
}

WebUI.click(findTestObject('Object Repository/CMS/Cart/CountGoods/div_minus_count_goods'))

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods'), 'value')

println(count)

if (count != '1') {
    throw new StepErrorException('Не уменьшается кол-во товара!')
}

WebUI.setText(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods'), DataCatalog.count)

count = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods'), 'value')

println(count)

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_buy_on_detail'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_road_to_cart'))

count1 = WebUI.getAttribute(findTestObject('Object Repository/CMS/Cart/CountGoods/input_count_goods_cart'), 'value')

println(count1)

if (count1 != DataCatalog.count) {
    throw new StepErrorException('Количество товара несовпадает!')
}

