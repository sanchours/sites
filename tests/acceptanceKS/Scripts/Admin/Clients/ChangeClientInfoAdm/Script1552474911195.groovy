/**
 * @description Изменение данных клиента из админки
 * @step Переход на детальную клиента, заполнение полей и сохранение
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
import data.OrdersData as OrdersData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/img_edit_first_client'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_name_client_adm'),DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_postcode_client_adm'),DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_address_client_adm'),DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_phone_client_adm'), DataOrders.tel)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/textarea_user_info_adm'),DataOrders.text)

WebUI.click(findTestObject('Object Repository/CMS/Clients/DetailClient/span_save_detail_client_adm'))

name = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_name_first_client'))

if (name != DataOrders.name){
	throw new StepErrorException('Имя на списковой не соответствует!')
}

email = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_login_first_client'))

if (email != DataOrders.email){
	throw new StepErrorException('Почта на списковой не соответствует!')
}

tel = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_tel_first_client'))

if (tel != DataOrders.tel){
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

if (infod != DataOrders.text){
	throw new StepErrorException('Текст на детальной не соответствует!')
}

DataOrders.setChangeData()

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_name_client_adm'),DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_postcode_client_adm'),DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_address_client_adm'),DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_phone_client_adm'), DataOrders.tel)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/textarea_user_info_adm'),DataOrders.text)

WebUI.click(findTestObject('Object Repository/CMS/Clients/DetailClient/span_save_detail_client_adm'))

name = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_name_first_client'))

if (name != DataOrders.name){
	throw new StepErrorException('Имя на списковой не соответствует!')
}

email = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_login_first_client'))

tel = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ClientList/div_tel_first_client'))

if (tel != DataOrders.tel){
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

if (infod != DataOrders.text){
	throw new StepErrorException('Текст на детальной не соответствует!')
}
