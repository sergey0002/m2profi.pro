const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const page = await browser.newPage();
  
  try {
    // Открываем страницу авторизации
    await page.goto('https://doc.m2profi.pro.test/sahmatka/ctrind.php?ctr=doc&act=index');
    
    // Проверяем, что открыта страница авторизации
    await page.waitForSelector('input[name="login"]');
    await page.waitForSelector('input[name="password"]');
    
    // Заполняем форму авторизации
    await page.fill('input[name="login"]', 'admin');
    await page.fill('input[name="password"]', '89039111405');
    
    // Нажимаем кнопку "Войти"
    const submitButton = await page.$('button[type="submit"]');
    if (submitButton) {
      await submitButton.click();
    } else {
      // Если кнопка не найдена по типу, пробуем найти по тексту
      await page.click('text="Войти в кабинет"');
    }
    
    // Ждем некоторое время для обработки авторизации
    await page.waitForTimeout(5000);
    
    // Проверяем, что мы на нужной странице после авторизации
    await page.waitForSelector('#files', { timeout: 10000 });
    
    console.log('Успешно авторизовались на сайте');
    
    // Ждем загрузки дерева документов
    await page.waitForSelector('#files');
    
    console.log('Страница с документами загружена');
    
    // Кейс 1: Поиск существующего файла
    console.log('Тест 1: Поиск существующего файла');
    await page.fill('#doc-search-input', 'договор');
    await page.waitForTimeout(3000); // Ждем 3 секунды для обновления дерева
    
    // Проверяем, что дерево обновилось с результатами
    const searchResults = await page.$$('.tfile');
    console.log(`Найдено файлов по запросу "договор": ${searchResults.length}`);
    
    // Проверяем, что дерево обновилось и не содержит ошибок
    const treeContent = await page.$eval('#files', el => el.innerHTML);
    if (treeContent.includes('ошибка') || treeContent.includes('Error') || treeContent.includes('Fatal')) {
      throw new Error('Обнаружена ошибка в дереве после поиска существующего файла');
    }
    
    // Скриншот результата
    await page.screenshot({ path: 'search_existing_file.png' });
    console.log('Скриншот поиска существующего файла сохранен');
    
    // Кейс 2: Поиск несуществующего файла
    console.log('Тест 2: Поиск несуществующего файла');
    await page.fill('#doc-search-input', 'asdfghjkl');
    await page.waitForTimeout(3000); // Ждем 3 секунды для обновления дерева
    
    // Проверяем, что дерево показывает "Ничего не найдено" или пустое
    const noResultsText = await page.$eval('#files', el => el.textContent || '');
    const noResultsFound = noResultsText.includes('Ничего не найдено');
    console.log(`Результат поиска "asdfghjkl": ${noResultsFound ? 'Ничего не найдено' : 'Есть результаты'}`);
    
    // Проверяем, что дерево обновилось и не содержит ошибок
    const treeContent2 = await page.$eval('#files', el => el.innerHTML);
    if (treeContent2.includes('ошибка') || treeContent2.includes('Error') || treeContent2.includes('Fatal')) {
      throw new Error('Обнаружена ошибка в дереве после поиска несуществующего файла');
    }
    
    // Скриншот результата
    await page.screenshot({ path: 'search_nonexistent_file.png' });
    console.log('Скриншот поиска несуществующего файла сохранен');
    
    // Кейс 3: Кнопка "Очистить"
    console.log('Тест 3: Кнопка "Очистить"');
    await page.click('#doc-search-clear');
    await page.waitForTimeout(1000); // Ждем обновления
    
    // Проверяем, что поле поиска очистилось
    const searchInputValue = await page.inputValue('#doc-search-input');
    console.log(`Значение в поле поиска после очистки: "${searchInputValue}"`);
    
    // Проверяем, что дерево вернулось к исходному состоянию
    const treeContentAfterClear = await page.$eval('#files', el => el.innerHTML);
    if (treeContentAfterClear.includes('ошибка') || treeContentAfterClear.includes('Error') || treeContentAfterClear.includes('Fatal')) {
      throw new Error('Обнаружена ошибка в дереве после нажатия кнопки "Очистить"');
    }
    
    // Скриншот результата
    await page.screenshot({ path: 'after_clear_button.png' });
    console.log('Скриншот после нажатия кнопки "Очистить" сохранен');
    
    // Кейс 4: Поиск с пустым запросом
    console.log('Тест 4: Поиск с пустым запросом');
    await page.fill('#doc-search-input', '');
    await page.waitForTimeout(3000); // Ждем 3 секунды для обновления дерева
    
    // Проверяем, что дерево вернулось к полному состоянию
    const allFilesCount = await page.$$('.tfile');
    console.log(`Количество файлов после очистки запроса: ${allFilesCount.length}`);
    
    // Проверяем, что дерево обновилось и не содержит ошибок
    const treeContent3 = await page.$eval('#files', el => el.innerHTML);
    if (treeContent3.includes('ошибка') || treeContent3.includes('Error') || treeContent3.includes('Fatal')) {
      throw new Error('Обнаружена ошибка в дереве после пустого поиска');
    }
    
    // Скриншот результата
    await page.screenshot({ path: 'after_empty_search.png' });
    console.log('Скриншот после пустого поиска сохранен');
    
    console.log('Все тесты выполнены успешно!');
    
  } catch (error) {
    console.error('Ошибка при выполнении тестов:', error);
    await page.screenshot({ path: 'error_screenshot.png' });
    throw error;
  }
  
 // Не закрываем браузер для возможности ручного тестирования
  // await browser.close();
})();
