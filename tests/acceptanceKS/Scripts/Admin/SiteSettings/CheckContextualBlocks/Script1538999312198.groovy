/**
 * @description Проверка на изменение положения блоков контента
 * @step Переходим на главную страницу в древе разделов
 * @step Переходим в редактор областей
 * @step Запоминаем URL страницы 
 * @step Переходим на главную страницу пользователькой части
 * @step Ищем блок 1 с определенным классом 1
 * @step Ищем блок 2 с определенным классом 2
 * @step Если второй блок находится выше первого выводим ошибку про несоответствие полей изначальным данным
 * @step Переходим в админскую часть
 * @step Ищем первую строчку с определенным текстом 1
 * @step Ищем вторую строчку с определенным текстом 2
 * @step Смотрим в каком браузере мы работаем, если не в хроме номер первой строчки перемащаем на одну позицию вверх
 * @spet Перемещаем вторую строчку на место первой
 * @spet Переходим на главную страницу
 * @spet Проверяем класс 2 у блока находящегося на месте блок 1
 * @step Проверяем класс 1 у блока находящегося на месте следующего за блок 1
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
import com.kms.katalon.core.testobject.TestObjectXpath
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import urlSite.BaseLink as BaseLink
import data.ServSetData as ServSetData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.openqa.selenium.By.ByXPath as ByXPath
import org.junit.After
import org.openqa.selenium.By
import org.openqa.selenium.WebDriver
import org.openqa.selenium.WebElement
import org.openqa.selenium.chrome.ChromeDriver
import com.kms.katalon.core.testobject.ConditionType
import com.kms.katalon.core.testobject.TestObjectXpath

ServSetData DataServSet = new ServSetData()

WebUI.click(findTestObject('CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('CMS/SiteSettings/div_main_area_editor'))

Url = WebUI.getUrl()

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebDriver driver = DriverFactory.getWebDriver()

int k = 0

int NumberBedit = 0

for (int Npereb1 = 1; k < 1; Npereb1++) {
	
	if (driver.findElement(By.xpath('//div[contains(@class,\'column__center-indent\')]/div[' + Npereb1 + ']')) .getAttribute("class") == DataServSet.class1) {
	
		k++		
		
		NumberBedit = Npereb1

	}
	
}

k = 0

int NumberBcateg = 0

for (int Npereb2 = 1; k < 1; Npereb2++) {
	
	if (driver.findElement(By.xpath('//div[contains(@class,\'column__center-indent\')]/div[' + Npereb2 + ']')) .getAttribute("class") == DataServSet.class2) {
	
		k++
		
		NumberBcateg = Npereb2
	}
	
}


if (NumberBedit >= NumberBcateg) {
	
	throw new StepErrorException("the initial position of the blocks corresponds to the initial conditions")
	
}


WebUI.navigateToUrl(Url)

WebUI.delay(10)  // не всегда прогружается список, необходимо добавить время на загрузку

k = 0

int NumberEditor = 0

for (int Npereb3 = 2; k < 1; Npereb3++) {
		
	if (driver.findElement(By.xpath('//tr[contains(@class,\'x-grid-group-body\')]//tr[' + Npereb3 + ']/td[contains(@class,\'sk-list-title \')]/div')).getText() == DataServSet.text1) {

		k++
		
		NumberEditor = Npereb3
		
	}
	
}

k = 0

int NumberCateg =0

for (int Npereb4 = 2; k < 1; Npereb4++) {
 
	if (driver.findElement(By.xpath('//tr[contains(@class,\'x-grid-group-body\')]//tbody/tr[' + Npereb4 + ']/td[contains(@class,\'sk-list-title \')]/div')).getText() == DataServSet.text2) {
	
		k++
		
		NumberCateg = Npereb4
		
	}
	
}

String myDriver = DriverFactory.getWebDriver()

myDriver = myDriver.substring(0,13)

if (myDriver != 'CChromeDriver') {

	NumberEditor--  //в mozille при DragAndDrop объект появляется ниже второго объекта, в Chrome выше. Сделано что бы в Mozille  работало

}
	TestObject to1 = new TestObject()
	to1.addProperty("xpath", ConditionType.EQUALS, '//tr[contains(@class,\'x-grid-group-body\')]//tbody/tr[' + NumberEditor + ']//td[contains(@class,\'sk-list-title \')]/div')

	TestObject to2 = new TestObject()
	to2.addProperty("xpath", ConditionType.EQUALS, '//tr[contains(@class,\'x-grid-group-body\')]//tbody/tr[' + NumberCateg + ']//td[contains(@class,\'sk-list-title \')]/div')

WebUI.dragAndDropToObject(to2,to1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

if (driver.findElement(By.xpath('//div[contains(@class,\'column__center-indent\')]/div[' + NumberBedit + ']')) .getAttribute("class") != DataServSet.class2) {
	
	throw new StepErrorException("the position of the blocks does not match the required position")
	
}

NumberBedit++

if (driver.findElement(By.xpath('//div[contains(@class,\'column__center-indent\')]/div[' + NumberBedit + ']')) .getAttribute("class") != DataServSet.class1) {
	
	throw new StepErrorException("the position of the blocks does not match the required position")
	
}



