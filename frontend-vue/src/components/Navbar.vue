

<template>
    <nav class="w-full">
        <div class="mx-auto px-4">
            <div class="text-white flex items-center justify-between h-12">
                <!-- Left side navigation -->
                <div class="flex items-center space-x-6">
                    <RouterLink class="hover:text-gray-400 text-sm font-medium transition-colors" to="/products">Product List</RouterLink>
                    <RouterLink class="hover:text-gray-400 text-sm font-medium transition-colors" to="/orders">My Orders</RouterLink>
                </div>

                <!-- Right side navigation -->
                <div class="flex items-center">
                    <RouterLink class="hover:text-gray-400 text-sm font-medium transition-colors pr-3" to="/cart">Cart</RouterLink>
                    <RouterLink class="text-gray-400 hover:text-white text-sm pl-3" style="border-left:1px solid grey" to="/login">Sign Out</RouterLink>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scope>
nav {
    font-size: 12px;
    text-align: center;
    font-size: 1rem;
    padding: 1rem 0;
}

nav a {
    display: inline-block;
}

nav a :hover {
    color: green;
}

nav a.router-link-exact-active {
    color: #0f8dfd;
}

nav a.router-link-exact-active:hover {
    background-color: transparent;
}



nav a:first-of-type {
    border: 0;
}


</style>