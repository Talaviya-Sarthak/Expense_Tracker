document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener("DOMContentLoaded", () => {
    const modeToggle = document.getElementById("modeToggle");
    const themeStylesheet = document.getElementById("themeStylesheet");

    // Load saved mode
    if (localStorage.getItem("theme") === "dark") {
      themeStylesheet.href = "../darkmode.css";
      modeToggle.checked = true;
    }

    modeToggle.addEventListener("change", () => {
      if (modeToggle.checked) {
        themeStylesheet.href = "../darkmode.css";
        localStorage.setItem("theme", "dark");
      } else {
        themeStylesheet.href = "../style.css";
        localStorage.setItem("theme", "light");
      }
    });
  });

  

  const menuIcon = document.querySelector('.menu-icon');
  const closeNavbarButton = document.querySelector('.close-navbar');
  const navbar = document.querySelector('.navbar');

  if (menuIcon && navbar && closeNavbarButton) {
    menuIcon.addEventListener('click', () => {
      navbar.classList.add('active');
    });

    closeNavbarButton.addEventListener('click', () => {
      navbar.classList.remove('active');
    });
  }

  const signupForm = document.getElementById('signup-form');
  if (signupForm) {
    signupForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const username = signupForm.querySelector('#username').value;
      const email = signupForm.querySelector('input[type="email"]').value;
      const phone = signupForm.querySelector('input[type="tel"]').value;
      const country = signupForm.querySelector('#country').value;
      const gender = signupForm.querySelector('#gender').value;
      const income = signupForm.querySelector('#income').value;
      const profilePic = signupForm.querySelector('#profile-pic').files[0];

      if (!username || !email || !phone || !country || !gender || !income) {
        alert('Please fill in all required fields.');
        return;
      }

      if (!validateEmail(email)) {
        alert('Please enter a valid email address.');
        return;
      }

      if (!validatePhoneNumber(phone)) {
        alert('Please enter a valid phone number (10 digits).');
        return;
      }

      const userData = {
        username: username,
        email: email,
        phone: phone,
        country: country,
        gender: gender,
        monthlyIncome: parseFloat(income),
        profilePic: profilePic ? URL.createObjectURL(profilePic) : null
      };
      localStorage.setItem('currentUser ', JSON.stringify(userData));
      localStorage.setItem('isSignedUp', 'true');

      alert('Signup successful! Please create your password.');
      window.location.href = '8password.html';
    });
  }

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
  }

  function validatePhoneNumber(phone) {
    const re = /^\d{10}$/;
    return re.test(String(phone));
  }

  const createPasswordForm = document.querySelector('form[action="4dashboard.html"]');
  if (createPasswordForm && window.location.pathname.includes('8password.html')) {
    createPasswordForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const newPassword = document.getElementById('createpassword').value;
      const confirmPassword = document.getElementById('confirmpassword').value;

      if (newPassword.length < 8) {
        alert('Password must be at least 8 characters long.');
        return;
      }

      if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
      }

      const currentUser = JSON.parse(localStorage.getItem('currentUser '));
      if (currentUser) {
        currentUser.password = newPassword;
        localStorage.setItem('currentUser ', JSON.stringify(currentUser));
        alert('Password created successfully! You can now log in.');
        window.location.href = '2login.html';
      } else {
        alert('Error: User data not found. Please sign up again.');
        window.location.href = '3signup.html';
      }
    });
  }

  const forgotPasswordForm = document.querySelector('form[action="2login.html"]');
  if (forgotPasswordForm && window.location.pathname.includes('9fpassword.html')) {
    forgotPasswordForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const otp = document.getElementById('otp').value;
      const newPassword = document.getElementById('createpassword').value;
      const confirmPassword = document.getElementById('confirmpassword').value;

      if (!otp) {
        alert('Please enter the OTP.');
        return;
      }

      if (newPassword.length < 8) {
        alert('New password must be at least 8 characters long.');
        return;
      }

      if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
      }

      const currentUser = JSON.parse(localStorage.getItem('currentUser '));
      if (currentUser) {
        currentUser.password = newPassword;
        localStorage.setItem('currentUser ', JSON.stringify(currentUser));
        alert('Password reset successfully! Please log in with your new password.');
        window.location.href = '2login.html';
      } else {
        alert('Error: User data not found. Please sign up or try again.');
        window.location.href = '3signup.html';
      }
    });
  }

  const loginForm = document.getElementById('loin-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const usernameInput = document.getElementById('username').value;
      const passwordInput = document.getElementById('password').value;

      const currentUser = JSON.parse(localStorage.getItem('currentUser '));

      if (currentUser && currentUser.username === usernameInput && currentUser.password === passwordInput) {
        localStorage.setItem('isLoggedIn', 'true');
        alert('Login successful!');
        window.location.href = '4dashboard.html';
      } else {
        alert('Invalid username or password.');
      }
    });
  }

  const addExpenseForm = document.querySelector('form[action="4dashboard.html"]');
  if (addExpenseForm && window.location.pathname.includes('6expense.html')) {
    addExpenseForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const category = document.getElementById('category').value;
      const customCategory = document.getElementById('custom_category').value;
      const amount = parseFloat(addExpenseForm.querySelector('input[name="rupees"]').value);
      const quantity = parseInt(addExpenseForm.querySelector('input[name="quantity"]').value);
      const description = addExpenseForm.querySelector('input[name="description"]').value;
      const date = addExpenseForm.querySelector('input[name="date"]').value;

      if (!category || amount <= 0 || quantity <= 0 || !date) {
        alert('Please fill in all required fields with valid values.');
        return;
      }

      const finalCategory = category === 'Other' ? customCategory : category;

      const transaction = {
        type: 'expense',
        category: finalCategory,
        amount: amount,
        quantity: quantity,
        description: description,
        date: date
      };

      let transactions = JSON.parse(localStorage.getItem('transactions')) || [];
      transactions.push(transaction);
      localStorage.setItem('transactions', JSON.stringify(transactions));

      alert('Expense added successfully!');
      window.location.href = '4dashboard.html';
    });
  }

  const addIncomeForm = document.querySelector('form[action="4dashboard.html"]');
  if (addIncomeForm && window.location.pathname.includes('7income.html')) {
    addIncomeForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const category = document.getElementById('income_category').value;
      const amount = parseFloat(document.getElementById('income_rupees').value);
      const description = document.getElementById('income_description').value;
      const date = document.getElementById('income_date').value;

      if (!category || amount <= 0 || !date) {
        alert('Please fill in all required fields with valid values.');
        return;
      }

      const transaction = {
        type: 'income',
        category: category,
        amount: amount,
        description: description,
        date: date
      };

      let transactions = JSON.parse(localStorage.getItem('transactions')) || [];
      transactions.push(transaction);
      localStorage.setItem('transactions', JSON.stringify(transactions));

      alert('Income added successfully!');
      window.location.href = '4dashboard.html';
    });
  }

  if (window.location.pathname.includes('4dashboard.html')) {
    const welcomeText = document.querySelector('.welcome-text');
    const totalExpenseCell = document.querySelector('.table-section tbody tr td:nth-child(1)');
    const totalIncomeCell = document.querySelector('.table-section tbody tr td:nth-child(2)');
    const totalBalanceCell = document.querySelector('.table-section tbody tr td:nth-child(3)');
    const lastUpdated = document.querySelector('.last-updated em');

    const currentUser = JSON.parse(localStorage.getItem('currentUser '));
    if (currentUser && welcomeText) {
      welcomeText.textContent = `Welcome, ${currentUser.username}`;
    }

    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    let totalExpense = 0;
    let totalIncome = 0;

    transactions.forEach(transaction => {
      if (transaction.type === 'expense') {
        totalExpense += transaction.amount;
      } else if (transaction.type === 'income') {
        totalIncome += transaction.amount;
      }
    });

    const totalBalance = totalIncome - totalExpense;

    if (totalExpenseCell) totalExpenseCell.textContent = `₹${totalExpense.toLocaleString()}`;
    if (totalIncomeCell) totalIncomeCell.textContent = `₹${totalIncome.toLocaleString()}`;
    if (totalBalanceCell) totalBalanceCell.textContent = `₹${totalBalance.toLocaleString()}`;

    if (lastUpdated) {
      const now = new Date();
      lastUpdated.textContent = `Last updated: ${now.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' })}`;
    }
  }

  if (window.location.pathname.includes('13histroy.html')) {
    const historyTableBody = document.querySelector('.table-section tbody');
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];

    if (historyTableBody) {
      historyTableBody.innerHTML = '';

      if (transactions.length === 0) {
        historyTableBody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
      } else {
        transactions.forEach(transaction => {
          const row = historyTableBody.insertRow();
          const typeClass = transaction.type === 'expense' ? 'expense-row' : 'income-row';
          row.classList.add(typeClass);

          row.insertCell().textContent = transaction.date;
          row.insertCell().textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
          row.insertCell().textContent = transaction.category;
          row.insertCell().textContent = `₹${transaction.amount.toLocaleString()}`;
          row.insertCell().textContent = transaction.description || '-';
        });
      }
    }

    const chartSection = document.querySelector('.chart-section');
    if (chartSection) {
      chartSection.innerHTML = '<canvas id="expenseIncomeChart"></canvas>';
      const ctx = document.getElementById('expenseIncomeChart');

      if (ctx) {
        const expenseCategories = {};
        const incomeCategories = {};

        transactions.forEach(transaction => {
          if (transaction.type === 'expense') {
            expenseCategories[transaction.category] = (expenseCategories[transaction.category] || 0) + transaction.amount;
          } else if (transaction.type === 'income') {
            incomeCategories[transaction.category] = (incomeCategories[transaction.category] || 0) + transaction.amount;
          }
        });

        const expenseLabels = Object.keys(expenseCategories);
        const expenseData = Object.values(expenseCategories);

        const incomeLabels = Object.keys(incomeCategories);
        const incomeData = Object.values(incomeCategories);

        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: [...expenseLabels, ...incomeLabels],
            datasets: [{
              label: 'Amount (₹)',
              data: [...expenseData, ...incomeData],
              backgroundColor: [
                'rgba(255, 99, 132, 0.8)', 'rgba(255, 159, 64, 0.8)', 'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(153, 102, 255, 0.8)'
              ],
              borderColor: [
                'rgba(255, 99, 132, 1)', 'rgba(255, 159, 64, 1)', 'rgba(255, 205, 86, 1)',
                'rgba(75, 192, 192, 1)', 'rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)'
              ],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              title: {
                display: true,
                text: 'Expense and Income Distribution by Category'
              }
            }
          }
        });
      }
    }
  }

  if (window.location.pathname.includes('10profile.html')) {
    const profileImg = document.querySelector('.profile-container .img-section img');
    const profileDetails = document.querySelectorAll('.profile-detail-box');
    const currentUser = JSON.parse(localStorage.getItem('currentUser '));

    if (currentUser) {
      if (profileImg && currentUser.profilePic) {
        profileImg.src = currentUser.profilePic;
      }
      if (profileDetails.length >= 4) {
        profileDetails[0].textContent = `Username: ${currentUser.username || ''}`;
        profileDetails[1].textContent = `Email-id: ${currentUser.email || ''}`;
        profileDetails[2].textContent = `Country: ${currentUser.country || ''}`;
        profileDetails[3].textContent = `Gender: ${currentUser.gender || ''}`;
      }
    }
  }

  const updateProfileForm = document.querySelector('form[action="10profile.html"]');
  if (updateProfileForm) {
    const currentUser = JSON.parse(localStorage.getItem('currentUser '));
    if (currentUser) {
      updateProfileForm.querySelector('input[name="username"]').value = currentUser.username || '';
      updateProfileForm.querySelector('input[name="email"]').value = currentUser.email || '';
      updateProfileForm.querySelector('input[name="country"]').value = currentUser.country || '';
      updateProfileForm.querySelector('#income').value = currentUser.monthlyIncome || '';
    }

    updateProfileForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const newUsername = updateProfileForm.querySelector('input[name="username"]').value;
      const newEmail = updateProfileForm.querySelector('input[name="email"]').value;
      const newCountry = updateProfileForm.querySelector('input[name="country"]').value;
      const newMonthlyIncome = parseFloat(updateProfileForm.querySelector('#income').value);

      if (!newUsername || !newEmail || !newCountry || isNaN(newMonthlyIncome)) {
        alert('Please fill in all fields correctly.');
        return;
      }

      if (!validateEmail(newEmail)) {
        alert('Please enter a valid email address.');
        return;
      }

      if (currentUser) {
        currentUser.username = newUsername;
        currentUser.email = newEmail;
        currentUser.country = newCountry;
        currentUser.monthlyIncome = newMonthlyIncome;
        localStorage.setItem('currentUser ', JSON.stringify(currentUser));
        alert('Profile updated successfully!');
        window.location.href = '10profile.html';
      } else {
        alert('Error: User data not found.');
      }
    });
  }

  if (window.location.pathname.includes('12DownloadExpenses.html')) {
    const downloadTableBody = document.querySelector('.table-section tbody');
    const filterSelect = document.getElementById('duration-filter');
    let allTransactions = JSON.parse(localStorage.getItem('transactions')) || [];

    const renderTable = (transactionsToDisplay) => {
      if (downloadTableBody) {
        downloadTableBody.innerHTML = '';

        if (transactionsToDisplay.length === 0) {
          downloadTableBody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
        } else {
          transactionsToDisplay.forEach(transaction => {
            const row = downloadTableBody.insertRow();
            row.insertCell().textContent = transaction.date;
            row.insertCell().textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
            row.insertCell().textContent = transaction.category;
            row.insertCell().textContent = `₹${transaction.amount.toLocaleString()}`;
            row.insertCell().textContent = transaction.description || '-';
          });
        }
      }
    };

    const filterTransactions = () => {
      const duration = filterSelect.value;
      let filtered = [];
      const now = new Date();

      if (duration === '') {
        filtered = allTransactions;
      } else {
        filtered = allTransactions.filter(transaction => {
          const transactionDate = new Date(transaction.date);
          switch (duration) {
            case 'day':
              return transactionDate.toDateString() === now.toDateString();
            case 'week':
              const firstDayOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
              const lastDayOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6));
              return transactionDate >= firstDayOfWeek && transactionDate <= lastDayOfWeek;
            case 'month':
              return transactionDate.getMonth() === now.getMonth() && transactionDate.getFullYear() === now.getFullYear();
            case 'year':
              return transactionDate.getFullYear() === now.getFullYear();
            default:
              return true;
          }
        });
      }
      renderTable(filtered);
    };

    filterSelect.addEventListener('change', filterTransactions);
    renderTable(allTransactions);

    const downloadPdfButton = document.querySelector('.download a[download="transaction_history.pdf"]');
    const downloadCsvButton = document.querySelector('.download a[download="transaction_history.csv"]');

    if (downloadPdfButton) {
      downloadPdfButton.addEventListener('click', (event) => {
        event.preventDefault();
        alert('PDF download initiated (functionality not fully implemented in this demo).');
      });
    }

    if (downloadCsvButton) {
      downloadCsvButton.addEventListener('click', (event) => {
        event.preventDefault();
        alert('CSV download initiated (functionality not fully implemented in this demo).');
        const headers = ["Date", "Type", "Category", "Amount", "Description"];
        const rows = allTransactions.map(t => [t.date, t.type, t.category, t.amount, t.description]);
        let csvContent = "data:text/csv;charset=utf-8,"
          + headers.join(",") + "\n"
          + rows.map(e => e.join(",")).join("\n");

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "transactions.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }
  }

  if (window.location.pathname.includes('14RecieptScanner.html')) {
    const dropArea = document.getElementById('drop-area');
    const fileElem = document.getElementById('fileElem');
    const filePreview = document.getElementById('file-preview');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, preventDefaults, false);
      document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, unhighlight, false);
    });

    dropArea.addEventListener('drop', handleDrop, false);

    const browseLabel = document.querySelector('.browse-label');
    if (browseLabel) {
      browseLabel.addEventListener('click', () => {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'fileElem';
        fileInput.accept = 'image/*,application/pdf';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        fileInput.addEventListener('change', (event) => {
          handleFiles(event.target.files);
          document.body.removeChild(fileInput);
        });
        fileInput.click();
      });
    }

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    function highlight() {
      dropArea.classList.add('highlight');
    }

    function unhighlight() {
      dropArea.classList.remove('highlight');
    }

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      handleFiles(files);
    }

    function handleFiles(files) {
      files = [...files];
      files.forEach(previewFile);
      alert(`File(s) uploaded: ${files.map(f => f.name).join(', ')} (functionality not fully implemented in this demo).`);
    }

    function previewFile(file) {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onloadend = function () {
        const img = document.createElement('img');
        img.src = reader.result;
        img.style.maxWidth = '100px';
        img.style.maxHeight = '100px';
        img.style.margin = '10px';
        filePreview.appendChild(img);
      };
    }
  }

  if (window.location.pathname.includes('15Savingschemes.html')) {
    const calculateButton = document.querySelector('.saving-schemes-container .form-buttons button');
    if (calculateButton) {
      calculateButton.addEventListener('click', () => {
        alert('Interest calculation is available in the full version. This is a demo.');
      });
    }
  }

}); ch