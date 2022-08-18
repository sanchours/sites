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

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_settings'))

if (WebUI.verifyElementChecked(findTestObject('CMS/Goods/CreateAssocietedGoods/input_associeted_goods'), 3, FailureHandling.OPTIONAL)) {
    WebUI.click(findTestObject('CMS/Goods/CreateAssocietedGoods/input_associeted_goods'))

    WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_save_settings'))
} else {
    WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_save_settings'))
}

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_goods'))

WebUI.doubleClick(findTestObject('CMS/Goods/CreateNewModifications/div_goods_detail'))

WebUI.click(findTestObject('CMS/Goods/CreateAssocietedGoods/button_associeted_goods'))

WebUI.click(findTestObject('CMS/Goods/CreateAssocietedGoods/button_edit_associeted_goods'))

GoodsName = WebUI.getText(findTestObject('CMS/Goods/CreateNewGoodsInSet/div_name_goods_in_set'))

WebUI.click(findTestObject('CMS/Goods/CreateNewGoodsInSet/button_edit_goods'))

WebUI.verifyTextPresent(GoodsName,true )

