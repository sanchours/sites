 /**
 * @description Проверка данных из формы
 * @step Переход в модуль "Заказы из форм"
 * @step Переход на детальную заказов из формы
 * @step Переход на детальную заказа
 * @step Проверка соответствия значений в заказе отправленным значениям
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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
import data.FormData as FormData

FormData DataForm = new FormData()

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Reviews/MaxLengthReview/div_control_panel'))

WebUI.click(findTestObject('CMS/Form/CheckFormData/div_orders_from_forms'))

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Form/CheckFormData/div_ordres_form_all_fields'))

WebUI.click(findTestObject('CMS/Form/CheckFormData/div_order_detail'))

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_drop_down_list'))

WebUI.verifyElementText(findTestObject('CMS/Form/CheckFormData/li_value_drop_down_list'), '2')

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_drop_down_list'))

WebUI.verifyElementAttributeValue(findTestObject('CMS/Form/CheckFormData/input_chekbox'), 'aria-checked', 'true', 1, FailureHandling.OPTIONAL)

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_checkbox_group'))

WebUI.verifyElementText(findTestObject('CMS/Form/CheckFormData/li_value_checkbox_group'), '2')

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_checkbox_group'))

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_switches_group'))

WebUI.verifyElementText(findTestObject('CMS/Form/CheckFormData/li_value_switches_group'), '2')

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_switches_group'))

WebUI.verifyElementAttributeValue(findTestObject('CMS/Form/CheckFormData/input_calender'), 'value', DataForm.date, 0)

WebUI.verifyElementAttributeValue(findTestObject('CMS/Form/CheckFormData/input_multiline_text_field'), 'value', DataForm.multilinetext, 
    0)

WebUI.verifyElementAttributeValue(findTestObject('CMS/Form/CheckFormData/input_password'), 'value', DataForm.password, 0)

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_rating'))

WebUI.verifyElementText(findTestObject('CMS/Form/CheckFormData/li_value_rating'), '2')

WebUI.click(findTestObject('CMS/Form/CheckFormData/input_rating'))

WebUI.verifyElementAttributeValue(findTestObject('CMS/Form/CheckFormData/input_text_field'), 'value', DataForm.text, 0)

