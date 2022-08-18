/**
 * @description Изменение данных клиента из ЛК
 * @step Открываем новое окно и переходим на главную
 * @step Переходим на страницу данных в ЛК 
 * @step Заполняем поля данных, сохраняем и проверяемчто данные сохранились
 * @step Переходим в админскую часть на страницу клиентов
 * @step Проверяем изменение данных на списковой части
 * @step Переход на детальную клиента и проверяем изменение данных на детальной
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
import org.openqa.selenium.Keys as Keys
import data.OrdersData as OrdersData
import urlSite.BaseLink as BaseLink
import org.openqa.selenium.JavascriptExecutor as JavascriptExecutor
import org.openqa.selenium.WebDriver as WebDriver
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

OrdersData DataOrders = new OrdersData()

WebDriver driver = DriverFactory.getWebDriver()

JavascriptExecutor js = ((driver) as JavascriptExecutor)

js.executeScript("window.open();")

//WebUI.sendKeys(null,Keys.chord(Keys.CONTROL,"t"))

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_to_profile'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_contact_details_tab'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_name_client_site'), DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_address_client_site'), DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_postcode_client_site'), DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_phone_client_site'), DataOrders.tel)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/button_save_client_info_site'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ChangePass/a_change_pass_tab'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_contact_details_tab'))

name = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_name_client_site'), 'value')

if (name != DataOrders.name){
	throw new StepErrorException('Имя в ЛК не соответствует!')
}

postcode = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_postcode_client_site'), 'value')

if (postcode != DataOrders.postcode){
	throw new StepErrorException('Индекс в ЛК не соответствует!')
}

address = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_address_client_site'), 'value')

if (address != DataOrders.address){
	throw new StepErrorException('Адрес в ЛК не соответствует!')
}

tel = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ProfileClient/input_phone_client_site'), 'value')

if (tel != DataOrders.tel){
	throw new StepErrorException('Телефон в ЛК не соответствует!')
}

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/div_clients'))

namel = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_name_first_client'))

if (namel != DataOrders.name){
	throw new StepErrorException('Имя на списковой не соответствует!')
}

emaill = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_login_first_client'))

if (emaill != DataOrders.email){
	throw new StepErrorException('Почта на списковой не соответствует!')
}

tell = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_tel_first_client'))

if (tell != DataOrders.tel){
	throw new StepErrorException('Номер на списковой не соответствует!')
}

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/img_edit_first_client'))

named = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/DetailClient/input_name_client_adm'), 'value')

if (named != DataOrders.name){
	throw new StepErrorException('Имя на детальной не соответствует!')
}

postcoded = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/DetailClient/input_postcode_client_adm'), 'value')

if (postcoded != DataOrders.postcode){
	throw new StepErrorException('Индекс на детальной не соответствует!')
}

addressd = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/DetailClient/input_address_client_adm'), 'value')

if (addressd != DataOrders.address){
	throw new StepErrorException('Адрес на детальной не соответствует!')
}

teld = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/DetailClient/input_phone_client_adm'), 'value')

if (teld != DataOrders.tel){
	throw new StepErrorException('Телефон на детальной не соответствует!')
}

infod = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/DetailClient/textarea_user_info_adm'), 'value')

if (infod != ''){
	throw new StepErrorException('Текст на детальной не соответствует!')
}


