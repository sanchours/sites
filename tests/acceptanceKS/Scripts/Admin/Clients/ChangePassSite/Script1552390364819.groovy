/**
 * @description Изменение пароля клиента из ЛК
 * @step Открываем новое окно и переходим на главную
 * @step Заходим в раздел изменения пароля в ЛК
 * @step Изменяем пароль и сохраняем изменения
 * @step Разлогиниваемя и входим в ЛК с новым паролем
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
import data.OrdersData as OrdersData
import urlSite.BaseLink as BaseLink
import org.openqa.selenium.JavascriptExecutor as JavascriptExecutor
import org.openqa.selenium.WebDriver as WebDriver
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory

OrdersData DataOrders = new OrdersData()

WebDriver driver = DriverFactory.getWebDriver()

JavascriptExecutor js = ((driver) as JavascriptExecutor)

js.executeScript("window.open();")

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_to_profile'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ChangePass/a_change_pass_tab'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ChangePass/input_oldpass_site'), DataOrders.pass)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ChangePass/input_newpass_site'), DataOrders.passchange)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ChangePass/input_newwpass_site'), DataOrders.passchange)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ChangePass/button_save_change_pass_site'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ChangePass/a_logout_site'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/AuthClient/a_login_as_user'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/AuthClient/input_auth_client_login'), DataOrders.email)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/AuthClient/input_auth_client_password'), DataOrders.passchange)

WebUI.click(findTestObject('Object Repository/CMS/Clients/AuthClient/button_come_in_auth'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_to_profile'), 3)