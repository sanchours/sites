/**
 * @description Создание нового раздела с заполненным "Текст раздела"
 * @step Жмем кнопку добавить раздел
 * @step Заполнение полей название и псевдоним раздела
 * @step Раскрытие списка родительский раздел и выбор второй строчки в выпадающем окне
 * @step Сохранение раздела
 * @step Заполняем поле "Текст раздела"
 * @step Сохраняем раздел
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
import data.SearchData as SearchData
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()
SearchData DataSearch = new SearchData()

WebUI.click(findTestObject('CMS/Tree/NewSection/add_section'))

WebUI.setText(findTestObject('CMS/News/CreateNewNews/CreateNewSection/input_title_section'), DataSearch.titlesection)

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_alias'), DataSearch.aliassection)

WebUI.click(findTestObject('CMS/Tree/NewSection/change_parent_section'))

WebUI.click(findTestObject('CMS/Tree/NewSection/li_top_menu'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/CreateNewSection/span_save_section'))

WebUI.delay(5)

WebUI.setText(findTestObject('Object Repository/CMS/Search/AdminPage/body_text_section_first'), DataCatalog.itemname)

WebUI.click(findTestObject('CMS/Sections/TextOfSection/button_save'))

WebUI.delay(1)

