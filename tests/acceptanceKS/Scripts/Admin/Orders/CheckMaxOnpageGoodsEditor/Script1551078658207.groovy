/**
 * @description Проверка кол-ва товаров при редактировании заказа
 * @step Заходим в редактирование закза
 * @step Проверяем кол-во товаров на странице
 */
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebDriver driver = DriverFactory.getWebDriver()

WebElement containerGoods = driver.findElement(By.xpath('//div[contains(@class,\'x-panel-body   x-grid-body x-panel-body-default x-panel-body-default x-docked-noborder-right x-docked-noborder-left x-layout-fit\')]'))

ArrayList<WebElement> Goods = new ArrayList<WebElement>()

Goods.addAll(containerGoods.findElements(By.xpath('//div[contains(@class,\'x-panel-body   x-grid-body x-panel-body-default x-panel-body-default x-docked-noborder-right x-docked-noborder-left x-layout-fit\')]//tr[contains(@class,\'x-grid-row\')]')))

int countGoods = Goods.size()

countGoods = countGoods

if (countGoods != 1) {
	throw new StepErrorException('Количество товара не 1!')
}











