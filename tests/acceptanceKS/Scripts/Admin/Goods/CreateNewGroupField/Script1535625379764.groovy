import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

WebUI.click(findTestObject('CMS/Goods/span_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_card_editor'))

WebUI.click(findTestObject('CMS/Goods/CreateNewGroupField/button_group'))

WebUI.click(findTestObject('CMS/Goods/CreateNewGroupField/button_edit_new_group'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewGroupField/input_name_group'), '88')

WebUI.setText(findTestObject('CMS/Goods/CreateNewGroupField/input_technical_name_group'), '88')

WebUI.click(findTestObject('CMS/Goods/CreateNewGroupField/div_type_group_list'))

WebUI.click(findTestObject('CMS/Goods/CreateNewGroupField/div_type_group_list_item'))

WebUI.click(findTestObject('CMS/Goods/CreateNewGroupField/button_save_group'))

