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

WebUI.click(findTestObject('CMS/Form/HiddenField/div_control_panel'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/div_modules'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/input_module_name'))

WebUI.click(findTestObject('Object Repository/CMS/Goods/span_all_module_layer'))

WebUI.delay(2)

WebUI.doubleClick(findTestObject('Object Repository/CMS/Goods/div_module_collection'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/button_install'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/span_ok'))

WebUI.refresh()

WebUI.delay(7)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_directory'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_new_directory'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_directory'), '8')

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name'), '8')

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_new_directory'))

WebUI.delay(3)

WebUI.click(findTestObject('Object Repository/CMS/Goods/div_collection'))

WebUI.click(findTestObject('Object Repository/CMS/Goods/button_new_collection'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_directory'), '8')

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name'), '8')

WebUI.click(findTestObject('Object Repository/CMS/Goods/button_save_new_collections'))

WebUI.click(findTestObject('CMS/Form/HiddenField/div_control_panel'))

WebUI.refresh()

WebUI.delay(7)

