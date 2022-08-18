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

price = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_first_goods_in_order'))

if (price != DataCatalog.price) {
	throw new StepErrorException('Изначальная цена товара не соответствует шаблону!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_first_orders_editor'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/input_price_order_editor'), DataCatalog.pricesec)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_name_first_orders_editor'))

WebUI.delay(3)

WebUI.takeScreenshot()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_cancel_order_editor'))

WebUI.takeScreenshot()

price2 = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_first_goods_in_order'))

if (price2 != DataCatalog.pricesec) {
	throw new StepErrorException('Цена не изменилась!')
}
