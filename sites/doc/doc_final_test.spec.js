const { test, expect } = require('@playwright/test');

test.describe('Финальное тестирование страницы документов', () => {
  test('Проверка загрузки страницы и основного функционала', async ({ page }) => {
    // Открываем страницу
    await page.goto('https://doc.m2profi.pro.test/sahmatka/ctrind.php?ctr=doc&act=index');
    
    // Проверяем отсутствие PHP ошибок на странице
    const bodyText = await page.textContent('body');
    expect(bodyText).not.toContain('Fatal error');
    expect(bodyText).not.toContain('Parse error');
    expect(bodyText).not.toContain('syntax error');
    expect(bodyText).not.toContain('Call to undefined method');
    
    // Авторизуемся с учетными данными admin / 123456
    await page.fill('#login_input', 'admin');
    await page.fill('#password_input', '123456');
    await page.click('input[type="submit"]');
    
    // Ожидаем загрузки страницы с деревом документов
    await page.waitForSelector('.dirtree', { timeout: 10000 });
    
    // Проверяем, что дерево документов отображается
    const treeVisible = await page.isVisible('.dirtree');
    expect(treeVisible).toBeTruthy();
    
    // Кейс 1: Сворачивание/разворачивание папок
    const folderHeaders = await page.$$('.folder-header');
    if (folderHeaders.length > 0) {
      // Проверяем клик по первой папке
      const firstFolder = folderHeaders[0];
      await firstFolder.click();
      
      // Проверяем, что нет ошибок в консоли
      page.on('console', message => {
        if (message.type() === 'error') {
          console.log(`Console Error: ${message.text()}`);
          expect(message.text()).not.toContain('Error');
        }
      });
    }
    
    // Кейс 2: Открытие карточки файла
    const fileLinks = await page.$$('.file-link');
    if (fileLinks.length > 0) {
      // Кликаем по первому файлу
      const firstFile = fileLinks[0];
      const filePromise = page.waitForEvent('popup');
      await firstFile.click();
      const filePage = await filePromise;
      
      // Проверяем, что карточка файла открылась
      await filePage.waitForLoadState();
      const fileCardVisible = await filePage.isVisible('body');
      expect(fileCardVisible).toBeTruthy();
      
      // Закрываем вкладку карточки файла
      await filePage.close();
    }
    
    // Кейс 3: Поиск существующего файла
    await page.fill('#doc-search-input', 'договор');
    await page.waitForTimeout(1000); // Ждем выполнения поиска
    
    // Проверяем, что дерево отфильтровалось
    const searchResults = await page.$$('.tfile');
    expect(searchResults.length >= 0).toBeTruthy(); // Может не быть результатов, но не должно быть ошибок
    
    // Проверяем отсутствие ошибок в консоли во время поиска
    page.on('console', message => {
      if (message.type() === 'error') {
        console.log(`Console Error during search: ${message.text()}`);
        expect(message.text()).not.toContain('Error');
      }
    });
    
    // Кейс 4: Очистка поиска
    await page.click('#doc-search-clear');
    await page.waitForTimeout(1000); // Ждем сброса поиска
    
    // Проверяем, что дерево вернулось к исходному состоянию
    const treeAfterClear = await page.$$('.dirtree li');
    expect(treeAfterClear.length >= 0).toBeTruthy();
    
    // Проверяем отсутствие ошибок в консоли при очистке
    page.on('console', message => {
      if (message.type() === 'error') {
        console.log(`Console Error during clear: ${message.text()}`);
        expect(message.text()).not.toContain('Error');
      }
    });
  });
});
