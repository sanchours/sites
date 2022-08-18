/**
 * @description Редактирование полей справочника
 * @step Переход в каталог
 * @step Переход в модуль "Справочники"
 * @step Нажатие на кнопку "Добавить справочник"
 * @step Заполнение поля "Название справочника"
 * @step Нажтие на кнопку "Сохранить"
 * @step Нажатие на кнопку "Структура"
 * @step Нажатие на кнопку "Добавить"
 * @step Заполнение поля "Название поля"
 * @step Заполнение поля "Тип отображения"
 * @step Нажатие на кнопку "Сохранить"
 * @step Нажатие на кнопку "Назад"
 * @step Нажатие на кнопку "Добавить"
 * @step Запонение всех полей указанных в структуре
 * @step Нажатие на кнопку "Сохранить"
 * @step Переход на детальную созданного поля
 * @step Редактирование значений полей 
 * @step Сохранение изменений
 */

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
import com.sun.org.apache.xalan.internal.xsltc.compiler.If
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import data.GoodsData as GoodsData

GoodsData DataGoods = new GoodsData()

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_directory'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_new_directory'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_directory'), '8')

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name'), '8')

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_new_directory'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/span_structure'))

WebDriver driver = DriverFactory.getWebDriver()

//WebUI.takeScreenshot()
//
//driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table/tbody/tr[3]/td[5]/div/img[1]')).click() //ddd

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_create_new_field_in_structure'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name_field_in_structure'), '1')

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_view_type_field_in_structure'))

ArrayList<WebElement> listTypeField = driver.findElements(By.cssSelector('.list-ct ul li'))

int countListTypeField = listTypeField.size()

println(countListTypeField)

nameField = driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).getText()

driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).click()

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_field_in_structure'), nameField)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

for (int numberField = 2; numberField <= countListTypeField; numberField++) {
		
	WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_create_new_field_in_structure'))

	WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_view_type_field_in_structure'))

	nameField = driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).getText()

	driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).click()

	WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_field_in_structure'), nameField)

	switch (nameField) {
		case 'справочник':
			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_additional_parametrs_in_structure'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()

			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

			break
		case 'галерея':
			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_additional_parametrs_in_structure'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()

			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

			break
		default:
			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

			break
	}
}

numberEditField = 3;

WebUI.delay(5)

for (int numberField = countListTypeField; numberField >= 2; numberField--) {
	
	numberEditField = numberEditField + 1;

//	WebUI.takeScreenshot()
	
	WebUI.delay(3)
	
	driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table/tbody/tr['+ numberEditField +']/td[5]/div/img[1]')).click()

	WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_view_type_field_in_structure'))

	nameField = driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).getText()

	driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).click()

	WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_field_in_structure'), nameField)

	switch (nameField) {
		case 'справочник':
			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_additional_parametrs_in_structure'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(1)

			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

			WebUI.delay(3)
			break
		case 'галерея':
			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_additional_parametrs_in_structure'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(1)

			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

			WebUI.delay(3)
			break
		default:
		
			WebUI.delay(1)
			
			WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))
			
			WebUI.delay(3)

			break
	}
}