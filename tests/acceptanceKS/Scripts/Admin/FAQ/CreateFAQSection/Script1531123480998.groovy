 /**
 * @description Создание раздела вопросы и ответы
 * @step Жмем кнопку добавить раздел
 * @step Заполнение полей название и псевдоним раздела
 * @step Раскрытие списка тип раздела и выбор шестой строчки в выпадающем окне
 * @step Раскрытие списка родительский раздел и выбор второй строчки в выпадающем окне
 * @step Сохранение раздела
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
import data.FAQData as FAQData

FAQData DataFAQ = new FAQData()

WebUI.click(findTestObject('CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/CreateNewSection/span_add_section'))

WebUI.setText(findTestObject('CMS/News/CreateNewNews/CreateNewSection/input_title_section'), DataFAQ.titlesection)

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_alias'), DataFAQ.aliassection)

WebUI.click(findTestObject('CMS/News/CreateNewNews/CreateNewSection/span_section_type'))

WebUI.click(findTestObject('CMS/FAQ/CreateFAQSection/span_section_type_faq'))

WebUI.click(findTestObject('CMS/Tree/NewSection/change_parent_section'))

WebUI.click(findTestObject('CMS/Tree/NewSection/li_top_menu'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/CreateNewSection/span_save_section'))

