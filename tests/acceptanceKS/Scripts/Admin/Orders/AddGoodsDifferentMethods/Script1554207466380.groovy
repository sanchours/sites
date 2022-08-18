/**
 * @description Добавление товара в заказ разными методами
 * @step Переход на детальную заказа в изменение заказа 
 * @step Проверяем что у нас один товар
 * @step Переходим на добавление товаров и считаем их количество
 * @step Делаем двойной клик по имени первого товара и проверяем что он добавился в список заказа
 * @step Удаляем второй товар в заказе
 * @step Переходим на добавление товаров и выбираем созданный нами раздел, добавляем товар из выбранного раздела
 * @step Проверяем что товар добавился, удаляем второй товар из заказа
 * @step Переходим на добавление товаров, выбираем все товары в списке и добавляем в заказ 
 * @step Проверяем что все товары добавлены в заказ
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import org.openqa.selenium.Keys as Keys

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_orders_editor'), 1)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebDriver driver = DriverFactory.getWebDriver()

WebElement containerGoods = driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table'))

ArrayList<WebElement> Goods = new ArrayList<WebElement>()

Goods.addAll(containerGoods.findElements(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table//tr')))

int countGoods = Goods.size() //берется кол-во со строчкой заголовка

println(countGoods)

WebUI.doubleClick(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_name_first_odrer_editor_list'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_orders_editor'), 2)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/img_del_second_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/DeleteOrders/span_accept_delete'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_orders_editor'), 1)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_open_list_sections_order_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/a_last_section_order_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/img_add_in_order_last_goods'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_orders_editor'), 2)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/img_del_second_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/DeleteOrders/span_accept_delete'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_orders_editor'), 1)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_select_all_goods_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_order_editor'))

WebUI.delay(5)

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_second_orders_editor'), 2)

WebElement containerGoods2 = driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]/div[contains(@class,\'x-panel-body\')]'))

ArrayList<WebElement> Goods2 = new ArrayList<WebElement>()

Goods2.addAll(containerGoods2.findElements(By.xpath('//div[contains(@class,\'sk-tab-list\')]/div[contains(@class,\'x-panel-body\')]//tr')))

int countGoods2 = Goods2.size()

countGoods2 = countGoods2 - 1

println(countGoods2)

if (countGoods2 != countGoods){
	throw new StepErrorException('Ошибка!')
}
