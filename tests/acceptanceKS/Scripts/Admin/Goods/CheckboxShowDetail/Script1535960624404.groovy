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
import urlSite.BaseLink as BaseLink

WebUI.click(findTestObject('CMS/Goods/span_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_card_editor'))

WebUI.doubleClick(findTestObject('CMS/Goods/ChecboxShowDetail/div_additional_card_detail'))

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Goods/ChecboxShowDetail/button_parametrs'))

WebUI.delay(2)

if (WebUI.verifyElementNotChecked(findTestObject('CMS/Goods/ChecboxShowDetail/checkbox_show_detail'), 1, FailureHandling.OPTIONAL) ==
	true) {
			
		WebUI.click(findTestObject('CMS/Goods/ChecboxShowDetail/checkbox_show_detail'))
	}

WebUI.click(findTestObject('CMS/Goods/ChecboxShowDetail/button_save'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

if (WebUI.verifyElementNotPresent(findTestObject('CMS/Goods/ChecboxShowDetail/div_detail_on page'), 0)) {} else {}

