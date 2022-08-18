/**
 * @description Добавления данных клиенту
 * @step Переходим на детальную клиента
 * @step Редактируем данные и сохраняем изменения
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

OrdersData DataOrders = new OrdersData()

if (WebUI.getUrl() != BaseLink.getUrlDef() + '/admin/#out.left.tools=Auth'){
	WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

	WebUI.click(findTestObject('Object Repository/CMS/Clients/div_clients'))
}

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/img_edit_first_client'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_name_client_adm'),DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_postcode_client_adm'),DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_address_client_adm'),DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/input_phone_client_adm'), DataOrders.tel)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/DetailClient/textarea_user_info_adm'),DataOrders.text)

WebUI.click(findTestObject('Object Repository/CMS/Clients/DetailClient/span_save_detail_client_adm'))

WebUI.click(findTestObject('Object Repository/CMS/Goods/NumberGoodsOnThePage/div_section'))





