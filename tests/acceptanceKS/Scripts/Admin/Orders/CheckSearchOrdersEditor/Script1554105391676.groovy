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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import org.openqa.selenium.Keys as Keys
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebDriver driver = DriverFactory.getWebDriver()

WebElement containerGoods = driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table'))

ArrayList<WebElement> Goods = new ArrayList<WebElement>()

Goods.addAll(containerGoods.findElements(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table//tr[contains(@class,\'x-grid-row\')]')))
	
int countGoods = Goods.size()
WebUI.takeScreenshot()
println(countGoods)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/input_search_name_orders_editor'), DataCatalog.itemnamesec)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_search_start_orders_editor'))

WebUI.delay(10)
//WebUI.waitForElementNotVisible(findTestObject('Object Repository/CMS/div_window_load'), 10)

WebUI.takeScreenshot()

WebElement containerGoods2 = driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table'))

ArrayList<WebElement> Goods2 = new ArrayList<WebElement>()

Goods2.addAll(containerGoods2.findElements(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table//tr[contains(@class,\'x-grid-row\')]')))

int countGoods2 = Goods2.size()

println(countGoods2)

if (countGoods2 != 1) {
	throw new StepErrorException('Результатов поиска не 1!')
}






