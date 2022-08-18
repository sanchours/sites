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

WebUI.click(findTestObject('CMS/MainPage/GoToSettingsInterface/div_top_menu'))

WebUI.click(findTestObject('CMS/MainPage/GoToSettingsInterface/div_main_page'))

WebUI.click(findTestObject('CMS/MainPage/ReviewOnMain/div_region_editor'))

nameClass = WebUI.getAttribute(findTestObject('CMS/MainPage/ReviewOnMain/div_activate_review'), 'class')

if (nameClass.contains('icon-saved') == true) {
    WebUI.click(findTestObject('CMS/MainPage/ReviewOnMain/div_activate_review'))
}

WebUI.click(findTestObject('CMS/MainPage/ReviewOnMain/div_section_pokup'))

WebUI.click(findTestObject('CMS/MainPage/ReviewOnMain/div_section_review'))

WebUI.click(findTestObject('CMS/MainPage/ReviewOnMain/div_tab_review'))

nameChekboxClass = WebUI.getAttribute(findTestObject('CMS/MainPage/ReviewOnMain/checkbox_on_main'), 'class')

if (nameChekboxClass.contains('x-grid-checkheader-checked') == false) {
	WebUI.click(findTestObject('CMS/MainPage/ReviewOnMain/checkbox_on_main'))
}