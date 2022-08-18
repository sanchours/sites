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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import org.openqa.selenium.Keys as Keys
import data.OrdersData as OrdersData
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()
OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_article_second_goods_in_order'), 1)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/img_add_in_order_last_goods'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_cancel_order_editor'))

WebUI.takeScreenshot()

namesec = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_name_second_goods_in_order'))

if (namesec != DataCatalog.itemnamesec) {
	throw new StepErrorException('Имя не совпадает с шаблоном!')
}

artsec = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_article_second_goods_in_order'))

if (artsec != DataCatalog.tecnicalnamesec) {
	throw new StepErrorException('Артикл не совпадает с шаблоном!')
}

pricesec = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_goods_in_order'))

if (pricesec != DataCatalog.pricesec) {
	throw new StepErrorException('Цена не совпадает с шаблоном!')
}


