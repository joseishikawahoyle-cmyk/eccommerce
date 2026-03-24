import requests
import sys
import json
from datetime import datetime
import time

class EcommerceAPITester:
    def __init__(self, base_url="https://qr-payment-hub-11.preview.emergentagent.com/api"):
        self.base_url = base_url
        self.admin_token = None
        self.tests_run = 0
        self.tests_passed = 0
        self.test_results = []
        self.created_product_id = None
        self.created_order_id = None

    def log_result(self, test_name, success, details="", error=""):
        """Log test result"""
        self.tests_run += 1
        if success:
            self.tests_passed += 1
            print(f"✅ {test_name} - PASSED")
        else:
            print(f"❌ {test_name} - FAILED: {error}")
        
        self.test_results.append({
            "test": test_name,
            "success": success,
            "details": details,
            "error": error
        })

    def run_test(self, name, method, endpoint, expected_status, data=None, headers=None, files=None):
        """Run a single API test"""
        url = f"{self.base_url}/{endpoint}"
        test_headers = {'Content-Type': 'application/json'}
        
        if headers:
            test_headers.update(headers)
        
        if self.admin_token and 'Authorization' not in test_headers:
            test_headers['Authorization'] = f'Bearer {self.admin_token}'

        try:
            if method == 'GET':
                response = requests.get(url, headers=test_headers, timeout=30)
            elif method == 'POST':
                if files:
                    # Remove Content-Type for file uploads
                    test_headers.pop('Content-Type', None)
                    response = requests.post(url, files=files, headers=test_headers, timeout=30)
                else:
                    response = requests.post(url, json=data, headers=test_headers, timeout=30)
            elif method == 'PUT':
                response = requests.put(url, json=data, headers=test_headers, timeout=30)
            elif method == 'DELETE':
                response = requests.delete(url, headers=test_headers, timeout=30)

            success = response.status_code == expected_status
            
            if success:
                try:
                    response_data = response.json() if response.content else {}
                    self.log_result(name, True, f"Status: {response.status_code}")
                    return True, response_data
                except:
                    self.log_result(name, True, f"Status: {response.status_code}")
                    return True, {}
            else:
                try:
                    error_detail = response.json().get('detail', 'Unknown error')
                except:
                    error_detail = response.text[:200]
                self.log_result(name, False, error=f"Expected {expected_status}, got {response.status_code}: {error_detail}")
                return False, {}

        except Exception as e:
            self.log_result(name, False, error=f"Request failed: {str(e)}")
            return False, {}

    def test_api_health(self):
        """Test API health check"""
        print("\n🔍 Testing API Health...")
        return self.run_test("API Health Check", "GET", "", 200)

    def test_brand_settings(self):
        """Test brand settings endpoint"""
        print("\n🔍 Testing Brand Settings...")
        return self.run_test("Get Brand Settings", "GET", "brand", 200)

    def test_admin_login(self):
        """Test admin login"""
        print("\n🔍 Testing Admin Authentication...")
        success, response = self.run_test(
            "Admin Login",
            "POST",
            "admin/login",
            200,
            data={"email": "admin@test.com", "password": "admin123"}
        )
        
        if success and 'token' in response:
            self.admin_token = response['token']
            print(f"✅ Admin token obtained: {self.admin_token[:20]}...")
            return True
        else:
            print("❌ Failed to get admin token")
            return False

    def test_admin_stats(self):
        """Test admin stats endpoint"""
        print("\n🔍 Testing Admin Stats...")
        if not self.admin_token:
            self.log_result("Admin Stats", False, error="No admin token available")
            return False
        
        return self.run_test("Admin Stats", "GET", "admin/stats", 200)

    def test_products_endpoints(self):
        """Test products endpoints"""
        print("\n🔍 Testing Products Endpoints...")
        
        # Get all products
        success, products = self.run_test("Get Products", "GET", "products", 200)
        if not success:
            return False
        
        # Get categories
        self.run_test("Get Categories", "GET", "categories", 200)
        
        # Test product search
        self.run_test("Search Products", "GET", "products?search=test", 200)
        
        # Test category filter
        if products and len(products) > 0:
            first_product = products[0]
            if 'category' in first_product:
                category = first_product['category']
                self.run_test("Filter by Category", "GET", f"products?category={category}", 200)
            
            # Test individual product
            if 'id' in first_product:
                product_id = first_product['id']
                self.run_test("Get Product Detail", "GET", f"products/{product_id}", 200)
        
        return True

    def test_admin_products_crud(self):
        """Test admin products CRUD operations"""
        print("\n🔍 Testing Admin Products CRUD...")
        
        if not self.admin_token:
            self.log_result("Admin Products CRUD", False, error="No admin token available")
            return False
        
        # Get admin products
        success, products = self.run_test("Admin Get Products", "GET", "admin/products", 200)
        if not success:
            return False
        
        # Create a test product
        test_product = {
            "name": "Test Product API",
            "description": "Test product created via API testing",
            "price": 99.99,
            "stock": 10,
            "category": "Test Category",
            "images": [],
            "is_active": True
        }
        
        success, created_product = self.run_test(
            "Create Product", 
            "POST", 
            "admin/products", 
            201, 
            data=test_product
        )
        
        if success and 'id' in created_product:
            self.created_product_id = created_product['id']
            print(f"✅ Created product with ID: {self.created_product_id}")
            
            # Update the product
            update_data = {
                "name": "Updated Test Product",
                "price": 149.99,
                "stock": 15
            }
            
            self.run_test(
                "Update Product",
                "PUT",
                f"admin/products/{self.created_product_id}",
                200,
                data=update_data
            )
            
            return True
        
        return False

    def test_orders_flow(self):
        """Test order creation and management flow"""
        print("\n🔍 Testing Orders Flow...")
        
        # First get available products
        success, products = self.run_test("Get Products for Order", "GET", "products", 200)
        if not success or not products:
            self.log_result("Orders Flow", False, error="No products available for testing")
            return False
        
        # Create an order with the first available product
        first_product = products[0]
        if 'id' not in first_product:
            self.log_result("Orders Flow", False, error="Product missing ID")
            return False
        
        order_data = {
            "items": [
                {
                    "product_id": first_product['id'],
                    "quantity": 1
                }
            ],
            "customer_name": "Test Customer",
            "customer_email": "test@example.com",
            "customer_phone": "987654321",
            "shipping_address": "Test Address 123, Lima, Peru",
            "payment_method": "yape"
        }
        
        success, created_order = self.run_test(
            "Create Order",
            "POST",
            "orders",
            201,
            data=order_data
        )
        
        if success and 'id' in created_order:
            self.created_order_id = created_order['id']
            print(f"✅ Created order with ID: {self.created_order_id}")
            
            # Get order details
            self.run_test(
                "Get Order Details",
                "GET",
                f"orders/{self.created_order_id}",
                200
            )
            
            return True
        
        return False

    def test_admin_orders(self):
        """Test admin orders management"""
        print("\n🔍 Testing Admin Orders Management...")
        
        if not self.admin_token:
            self.log_result("Admin Orders", False, error="No admin token available")
            return False
        
        # Get all orders
        success, orders = self.run_test("Admin Get Orders", "GET", "admin/orders", 200)
        if not success:
            return False
        
        # Test order filtering
        self.run_test("Filter Pending Orders", "GET", "admin/orders?status=pending_payment", 200)
        self.run_test("Filter Confirmed Orders", "GET", "admin/orders?status=confirmed", 200)
        
        return True

    def test_banners(self):
        """Test banners endpoint"""
        print("\n🔍 Testing Banners...")
        return self.run_test("Get Banners", "GET", "banners", 200)

    def test_file_upload(self):
        """Test file upload endpoint"""
        print("\n🔍 Testing File Upload...")
        
        # Create a simple test file
        test_content = b"Test file content for API testing"
        files = {'file': ('test.txt', test_content, 'text/plain')}
        
        success, response = self.run_test(
            "Upload File",
            "POST",
            "upload",
            200,
            files=files
        )
        
        if success and 'path' in response:
            # Test file retrieval
            file_path = response['path']
            self.run_test(
                "Get Uploaded File",
                "GET",
                f"files/{file_path}",
                200
            )
        
        return success

    def cleanup_test_data(self):
        """Clean up test data"""
        print("\n🧹 Cleaning up test data...")
        
        if self.created_product_id and self.admin_token:
            self.run_test(
                "Delete Test Product",
                "DELETE",
                f"admin/products/{self.created_product_id}",
                200
            )

    def run_all_tests(self):
        """Run all API tests"""
        print("🚀 Starting Ecommerce API Tests")
        print(f"📍 Testing against: {self.base_url}")
        print("=" * 60)
        
        # Test sequence
        tests = [
            self.test_api_health,
            self.test_brand_settings,
            self.test_admin_login,
            self.test_admin_stats,
            self.test_products_endpoints,
            self.test_banners,
            self.test_file_upload,
            self.test_admin_products_crud,
            self.test_orders_flow,
            self.test_admin_orders,
        ]
        
        for test in tests:
            try:
                test()
                time.sleep(0.5)  # Small delay between tests
            except Exception as e:
                print(f"❌ Test {test.__name__} failed with exception: {e}")
                self.log_result(test.__name__, False, error=str(e))
        
        # Cleanup
        self.cleanup_test_data()
        
        # Print summary
        print("\n" + "=" * 60)
        print("📊 TEST SUMMARY")
        print("=" * 60)
        print(f"Total Tests: {self.tests_run}")
        print(f"Passed: {self.tests_passed}")
        print(f"Failed: {self.tests_run - self.tests_passed}")
        print(f"Success Rate: {(self.tests_passed/self.tests_run*100):.1f}%" if self.tests_run > 0 else "0%")
        
        # Show failed tests
        failed_tests = [r for r in self.test_results if not r['success']]
        if failed_tests:
            print("\n❌ FAILED TESTS:")
            for test in failed_tests:
                print(f"  • {test['test']}: {test['error']}")
        
        return self.tests_passed == self.tests_run

def main():
    """Main test execution"""
    tester = EcommerceAPITester()
    success = tester.run_all_tests()
    
    # Save detailed results
    with open('/app/backend_test_results.json', 'w') as f:
        json.dump({
            'timestamp': datetime.now().isoformat(),
            'total_tests': tester.tests_run,
            'passed_tests': tester.tests_passed,
            'success_rate': (tester.tests_passed/tester.tests_run*100) if tester.tests_run > 0 else 0,
            'results': tester.test_results
        }, f, indent=2)
    
    return 0 if success else 1

if __name__ == "__main__":
    sys.exit(main())