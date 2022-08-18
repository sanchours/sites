import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import org.junit.After

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

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersList/input_name_search'), 'name')

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/div_start_search_name'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersList/div_second_order_list'), 1)

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_name_first_order')) != 'name'){
	throw new StepErrorException('Поиск по имени нашел заказ с другим именем!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/div_reset_search_name'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Orders/OrdersList/div_second_order_list'), 1)

id  = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_id_first_order_list'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersList/input_id_search'), id)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/div_start_search_id'))
	
WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersList/div_second_order_list'), 1)

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_id_first_order_list')) != id){
	 throw new StepErrorException('Поиск по id нашел заказ с другим id!')
}

