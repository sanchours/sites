/**
 * @description Изменение кол-ва товара в заказе
 * @step Проверяем начальное кол-во товара
 * @step Заходим в редактирование заказа и меняем кол-во товара
 * @step Возвращаемя на детальную и проверяем кол-во товара
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import org.openqa.selenium.Keys as Keys
import data.OrdersData as OrdersData
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()
OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

count = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_count_first_goods_in_order'))

if (count != '1') {
	throw new StepErrorException('Изначальное кол-во товара не 1!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_count_first_orders_editor'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/input_count_order_editor'), '2')

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_name_first_orders_editor'))

WebUI.delay(3)

WebUI.takeScreenshot()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_cancel_order_editor'))

WebUI.takeScreenshot()

count2 = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_count_first_goods_in_order'))

if (count2 != '2') {
	throw new StepErrorException('Кол-во товара не поменялось!')
}
